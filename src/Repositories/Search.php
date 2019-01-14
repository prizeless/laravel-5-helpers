<?php

namespace Laravel5Helpers\Repositories;

use function is_array;
use Laravel5Helpers\Exceptions\ResourceGetError;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Laravel5Helpers\Definitions\RelationSearch;
use Laravel5Helpers\Definitions\MaxValueSearch;
use Laravel5Helpers\Definitions\MinValueSearch;
use function strtotime;

abstract class Search extends Repository
{
    protected $startDate;

    protected $endDate;

    protected $startDateInverted;

    protected $endDateInverted;

    protected $startDateField = 'created_at';

    protected $endDateField = 'created_at';

    private $searchRelations;

    private $maxSearch = [];

    private $minSearch = [];

    const OR_SEARCH = 'orWhere';

    const AND_SEARCH = 'where';

    public function wildCardSearch(array $filters, array $search = [], array $relations = [])
    {
        try {
            $query = $this->getRelations($relations);
            $this->searchRelations($query);
            $this->addExactMatches($filters, $query);
            $this->addDates($query, self::AND_SEARCH);
            $this->addDatesInverted($query, self::AND_SEARCH);
            $this->addMinSearch($query, self::AND_SEARCH);
            $this->addMaxSearch($query, self::AND_SEARCH);

            $query = $query->where(function ($query) use ($search) {
                foreach ($search as $column => $value) {
                    $query = $query->like($column, $value);
                }
            });

            if (empty($this->order) === false) {
                return $query->orderBy($this->order->field, $this->order->direction)->paginate($this->pageSize);
            }

            return $query->paginate($this->pageSize);
        } catch (\PDOException $exception) {
            throw new ResourceGetError($this->getModelShortName());
        } catch (QueryException $exception) {
            throw new ResourceGetError($this->getModelShortName());
        }
    }

    public function wildCardOrSearch(array $filters, array $search = [], array $relations = [])
    {
        try {
            $query = $this->getRelations($relations);
            $this->searchOrRelations($query);
            $this->addExactMatches($filters, $query);
            $this->addDates($query, self::OR_SEARCH);
            $this->addDatesInverted($query, self::OR_SEARCH);
            $this->addMinSearch($query, self::OR_SEARCH);
            $this->addMaxSearch($query, self::OR_SEARCH);

            if (empty($filters) === true) {
                $query = $query->orWhere(function ($query) use ($search) {
                    foreach ($search as $column => $value) {
                        if (is_array($value) === true) {
                            $query = $query->orWhereIn($column, 'LIKE', $value);
                        } else {
                            $query = $query->orWhere($column, 'LIKE', "%$value%");
                        }
                    }
                });
            } else {
                $query = $query->where(function ($query) use ($search) {
                    foreach ($search as $column => $value) {
                        if (is_array($value) === true) {
                            $query = $query->orWhereIn($column, $value);
                        } else {
                            $query = $query->orWhere($column, 'LIKE', "%$value%");
                        }
                    }
                });
            }

            if (empty($this->order) === false) {
                return $query->orderBy($this->order->field, $this->order->direction)->paginate($this->pageSize);
            }

            return $query->paginate($this->pageSize);
        } catch (\PDOException $exception) {
            throw new ResourceGetError($this->getModelShortName());
        } catch (QueryException $exception) {
            throw new ResourceGetError($this->getModelShortName());
        }
    }

    protected function searchRelations(&$query)
    {
        if (empty($this->searchRelations) === false) {
            $query = $query->where(function ($query) {
                /**
                 * @var  $relation RelationSearch
                 */
                foreach ($this->searchRelations as $relation) {
                    $query = $query->whereHas($relation->relation, function ($query) use ($relation) {
                        if (is_array($relation->value) === true) {
                            $query->whereIn($relation->column, $relation->value);
                        } else {
                            $query->like($relation->column, $relation->value);
                        }
                    });
                }
            });
        }
    }

    protected function searchOrRelations(&$query)
    {
        if (empty($this->searchRelations) === false) {
            $query = $query->orWhere(function ($query) {
                /**
                 * @var  $relation RelationSearch
                 */
                foreach ($this->searchRelations as $relation) {
                    $query = $query->orWhereHas($relation->relation, function ($query) use ($relation) {
                        if (is_array($relation->value) === true) {
                            $query->whereIn($relation->column, $relation->value);
                        } else {
                            $query->whereWildCard($relation->column, $relation->value);
                        }
                    });
                }
            });
        }
    }

    public function addSearchRelations($relations)
    {
        $this->searchRelations = $relations;

        return $this;
    }

    protected function addDates(&$query, $searchType)
    {
        if (empty($this->startDate) === false && empty($this->endDate) === false) {
            $query->where(function ($query) use ($searchType) {
                $query->where($this->startDateField, '>=', $this->startDate)
                      ->where($this->endDateField, '<=', $this->endDate);
            });
        }

        if (empty($this->startDate) === true && empty($this->endDate) === false) {
            $query->{$searchType}($this->endDateField, '<=', $this->endDate);
        }

        if (empty($this->startDate) === false && empty($this->endDate) === true) {
            $query->{$searchType}($this->startDateField, '>=', $this->startDate);
        }
    }

    protected function addDatesInverted(&$query, $searchType)
    {
        if (empty($this->startDateInverted) === false && empty($this->endDateInverted) === false) {
            $query->where(function ($query) use ($searchType) {
                $query->where($this->startDateField, '<=', $this->startDateInverted)
                      ->where($this->endDateField, '>=', $this->endDateInverted);
            });
        }

        if (empty($this->startDateInverted) === true && empty($this->endDateInverted) === false) {
            $query->{$searchType}($this->endDateField, '>=', $this->endDateInverted);
        }

        if (empty($this->startDateInverted) === false && empty($this->endDateInverted) === true) {
            $query->{$searchType}($this->startDateField, '<=', $this->startDateInverted);
        }
    }

    protected function addMaxSearch(&$query, $searchType)
    {
        /**
         * @var $item MaxValueSearch
         */
        $query->where(function ($query) use ($searchType) {
            foreach ($this->maxSearch as $item) {
                $query->{$searchType}($item->fieldName, '<=', $item->maxValue);
            }
        });
    }

    protected function addMinSearch(&$query, $searchType)
    {
        /**
         * @var $item MinValueSearch
         */
        $query->where(function ($query) use ($searchType) {
            foreach ($this->minSearch as $item) {
                $query->{$searchType}($item->fieldName, '>=', $item->minValue);
            }
        });
    }

    public function setStartDate($date)
    {
        $timestamp       = strtotime($date);
        $this->startDate = Carbon::createFromTimestamp($timestamp)->toDateString();

        return $this;
    }

    public function setStartDateInverted($date)
    {
        $timestamp               = strtotime($date);
        $this->startDateInverted = Carbon::createFromTimestamp($timestamp)->toDateString();

        return $this;
    }

    public function setEndDate($date)
    {
        $timestamp     = strtotime($date);
        $this->endDate = Carbon::createFromTimestamp($timestamp)->toDateString();

        return $this;
    }

    public function setEndDateInverted($date)
    {
        $timestamp             = strtotime($date);
        $this->endDateInverted = Carbon::createFromTimestamp($timestamp)->toDateString();

        return $this;
    }

    public function setMinSearches(array $minSearches)
    {
        $this->minSearch = $minSearches;

        return $this;
    }

    public function setMaxSearches(array $maxSearches)
    {
        $this->maxSearch = $maxSearches;

        return $this;
    }

    protected function addExactMatches(array $filters, &$query)
    {
        $query = $query->where(function ($query) use ($filters) {
            foreach ($filters as $column => $value) {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }
        });
    }
}
