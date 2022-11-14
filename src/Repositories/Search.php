<?php

namespace Laravel5Helpers\Repositories;

use PDOException;
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

    private $searchRelations = [];

    private $maxSearch = [];

    private $minSearch = [];

    public function wildCardSearch(array $filters, array $search = [])
    {
        try {
            $query = $this->getRelations();
            $this->searchRelations($query);
            $this->addExactMatches($filters, $query);
            $this->addDates($query);
            $this->addDatesInverted($query);
            $this->addMinSearch($query);
            $this->addMaxSearch($query);

            $query = $query->where(function ($query) use ($search) {
                foreach ($search as $column => $value) {
                    $query = $query->like($column, $value);
                }
            });

            if (empty($this->order) === false) {
                return $query->orderBy($this->order->field, $this->order->direction)->paginate($this->pageSize);
            }

            return $query->paginate($this->pageSize);
        } catch (QueryException | PDOException $exception) {
            $this->logException($exception);
            throw new ResourceGetError($this->getModelShortName());
        }
    }

    public function wildCardOrSearch(array $filters, array $search = [])
    {
        try {
            $query = $this->getRelations();
            $this->searchOrRelations($query, $search);
            $this->addExactMatches($filters, $query);
            $this->addDates($query);
            $this->addDatesInverted($query);
            $this->addMinSearch($query);
            $this->addMaxSearch($query);

            if (empty($this->order) === false) {
                return $query->orderBy($this->order->field, $this->order->direction)->paginate($this->pageSize);
            }

            return $query->paginate($this->pageSize);
        } catch (QueryException | PDOException $exception) {
            $this->logException($exception);
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
                            if ($relation->operator == '=') {
                                $query->like($relation->column, $relation->value);
                            } else {
                                $query->where($relation->column, $relation->operator, $relation->value);
                            }
                        }
                    });
                }
            });
        }
    }

    protected function searchOrRelations(&$query, $search)
    {
        $query = $query->orWhere(function ($query) use ($search) {
            /**
             * @var  $relation RelationSearch
             */
            foreach ($this->searchRelations as $relation) {
                $query = $query->orWhereHas($relation->relation, function ($query) use ($relation) {
                    if (is_array($relation->value) === true) {
                        $query->whereIn($relation->column, $relation->value);
                    } else {
                        if ($relation->operator == '=') {
                            $query->whereWildCard($relation->column, $relation->value);
                        } else {
                            $query->where($relation->column, $relation->operator, $relation->value);
                        }
                    }
                });
            }

            foreach ($search as $column => $value) {
                if (is_array($value) === true) {
                    $query = $query->orWhereIn($column, $value);
                } else {
                    $query = $query->orWhere($column, 'LIKE', "%$value%");
                }
            }
        });
    }

    public function addSearchRelations($relations)
    {
        $this->searchRelations = $relations;

        return $this;
    }

    protected function addDates(&$query)
    {
        if (empty($this->startDate) === false && empty($this->endDate) === false) {
            $query->where(function ($query) {
                $query->where($this->startDateField, '>=', $this->startDate)
                      ->where($this->endDateField, '<=', $this->endDate);
            });
        }

        if (empty($this->startDate) === true && empty($this->endDate) === false) {
            $query->where($this->endDateField, '<=', $this->endDate);
        }

        if (empty($this->startDate) === false && empty($this->endDate) === true) {
            $query->where($this->startDateField, '>=', $this->startDate);
        }
    }

    protected function addDatesInverted(&$query)
    {
        if (empty($this->startDateInverted) === false && empty($this->endDateInverted) === false) {
            $query->where(function ($query) {
                $query->where($this->startDateField, '<=', $this->startDateInverted)
                      ->where($this->endDateField, '>=', $this->endDateInverted);
            });
        }

        if (empty($this->startDateInverted) === true && empty($this->endDateInverted) === false) {
            $query->where($this->endDateField, '>=', $this->endDateInverted);
        }

        if (empty($this->startDateInverted) === false && empty($this->endDateInverted) === true) {
            $query->where($this->startDateField, '<=', $this->startDateInverted);
        }
    }

    protected function addMaxSearch(&$query)
    {
        /**
         * @var $item MaxValueSearch
         */
        $query->where(function ($query) {
            foreach ($this->maxSearch as $item) {
                $query->where($item->fieldName, '<=', $item->maxValue);
            }
        });
    }

    protected function addMinSearch(&$query)
    {
        /**
         * @var $item MinValueSearch
         */
        $query->where(function ($query) {
            foreach ($this->minSearch as $item) {
                $query->where($item->fieldName, '>=', $item->minValue);
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
