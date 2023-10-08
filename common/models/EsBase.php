<?php
/**
 * @file EsBaseModel.php
 * @version 1.0
 * @author zhangfan <zhangfan01@yuanxin-inc.com>
 * @date 2020/5/27
 */

namespace common\models;

use Elasticsearch\ClientBuilder;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\db\ExpressionInterface;
use yii\helpers\ArrayHelper;

class EsBase
{
    protected static $_instance = null;

    /**
     * @var array hosts
     */
    protected $hosts = [];

    /**
     * @var string username
     */
    protected $username = '';

    /**
     * @var string password
     */
    protected $password = '';

    /**
     * @var string index
     */
    protected $index = '';

    /**
     * @var string type
     */
    protected $type = '_doc';

    /**
     * @var string routing
     */
    protected $routing;

    /**
     * @var array mapping
     */
    protected $mapping = [];

    /**
     * @var array
     */
    public $query = [];

    /**
     * @var int
     */
    public $limit = 10;

    /**
     * @var int
     */
    public $offset = 0;

    /**
     * @var array
     */
    public $orderBy = [];

    /**
     * @var bool
     */
    public $asArray = false;

    /**
     * @var array
     */
    public $where = [];

    /**
     * @var array
     */
    public $andWhere = [];

    private $client;

    /**
     * @var array
     */
    public $settings = [];

    public function __construct()
    {
        if (empty($this->hosts)) {
            throw new \Exception("hosts is empty");
        }
        if (empty($this->username)) {
            throw new \Exception("username is empty");
        }
        if (empty($this->password)) {
            throw new \Exception("password is empty");
        }
        if (empty($this->index)) {
            throw new \Exception("index is empty");
        }
        //$this->client = ClientBuilder::create()->setHosts($this->hosts)->build();
        $this->client = ClientBuilder::create()->setHosts($this->hosts)->setBasicAuthentication($this->username, $this->password)->build();
    }

    public static function getInstance()
    {
        if (static::$_instance === null) {
            return new static;
        }
        return static::$_instance;
    }

    public static function find()
    {
        if (static::$_instance === null) {
            return new static;
        }
        return static::$_instance;
    }

    /**
     * 创建索引
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/27
     */
    public function createEsIndex()
    {
        $params = [
            'index' => $this->index,
            'body'  => [
                'mappings' => $this->mapping,
            ],
        ];

        if ($this->routing) {
            $params['routing'] = $this->routing;
        }

        if (!empty($this->settings)) {
            $params['body']['settings'] = $this->settings;
        }

        $response = $this->client->indices()->create($params);
        return $response;
    }

    /**
     * 删除索引
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/27
     */
    public function deleteEsIndex()
    {
        $params   = ['index' => $this->index];
        $response = $this->client->indices()->delete($params);
        return $response;
    }

    /**
     * 设置映射
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/27
     */
    public function updateEsMapping()
    {
        if (!$this->existEsIndex()) {
            return $this->createEsIndex();
        }
        return true;
    }

    public function updateEsSetting()
    {
        $params = [
            'index' => $this->index,
            'type'  => $this->type,
            'body'  => [
                'settings' => $this->settings,
            ],
        ];

        $response = $this->client->indices()->putSettings($params);
        return $response;
    }

    /**
     * 获取映射
     * @return mixed
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/27
     */
    public function getEsMapping()
    {
        $params = [
            'index' => $this->index,
        ];

        $response = $this->client->indices()->getMapping($params);
        return $response;
    }

    /**
     * @return bool
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/27
     */
    public function existEsIndex()
    {
        $params = [
            'index' => $this->index,
        ];
        $response = $this->client->indices()->exists($params);
        return $response;
    }

    /**
     * @return array
     */
    public function info()
    {
        return $this->client->info();
    }

    /**
     * @return array
     * @throws NotSupportedException
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/27
     */
    public function search()
    {
        $query      = [];
        $whereQuery = $this->buildQueryFromWhere($this->where);
        if ($whereQuery) {
            $query = $whereQuery;
        } else if ($this->query) {
            $query = $this->query;
        }
        $params = [
            'index' => $this->index,
            'body'  => [
                'size' => $this->limit,
                'from' => $this->offset,
                'track_total_hits' => true,
            ],
        ];

        if (!empty($query)) {
            $params['body']['query'] = $query;
        }

        $sort = $this->buildOrderBy($this->orderBy);
        if (!empty($sort)) {
            $params['body']['sort'] = $sort;
        }

        $response = $this->client->search($params);
        $list     = ArrayHelper::getValue($response, 'hits.hits', []);
        $count    = ArrayHelper::getValue($response, 'hits.total.value', 0);

        // if (!$this->asArray && !empty($list)) {
        if (!empty($list)) {
            $list = array_column($list, '_source');
        }

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 检测分词结果
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-08-04
     * @version 1.0
     * @param   string     $content  [description]
     * @param   string     $analyzer [description]
     * @return  [type]               [description]
     */
    public function analyze($content = '',$analyzer = 'ik_max_word')
    {
        $keyword = $this->client->indices()->analyze(
            [
                'body' => [
                    'analyzer' => $analyzer,
                    'text'=> $content
                ]
            ]
        );
        if (isset($keyword['tokens']) && !empty($keyword['tokens'])) {
            return array_column($keyword['tokens'],'token');
        }
        return [];
    }


    /**
     * 搜索查询
     * @param $query
     * @param int $offset
     * @param int $limit
     * @param array $order
     * @return array
     */
    public static function search_find($query = [], $offset = 0, $limit = 20, $order = ['_score' => 'desc'], $highlight = [], $aggs = [])
    {
        $client = self::getInstance();
        $params = [
            'index' => $client->index,
            'type'  => $client->type,
        ];
        if (!empty($query)) {
            $params['body'] = [
                'query' => $query,
                'size'  => $limit,
                'from'  => $offset,
            ];
            if (!empty($order)) {
                $params['body']['sort'] = $order;
            }
        }

        if (!empty($highlight)) {
            $params['body']['highlight'] = $highlight;
        }

        if (!empty($aggs)) {
            $params['body']['aggs'] = $aggs;
        }
        $returnData = [];
        $result     = $client->client->search($params);
        if (!empty($aggs)) {
            $returnData = $result['aggregations']['recommend_title']['buckets'] ?? [];
        } else {
            $list = ArrayHelper::getValue($result, 'hits.hits', []);
            if (!empty($list)) {
                foreach ($list as $key => $item) {
                    $returnData[$key] = $item['_source'];
                    if (isset($item['highlight'])) {
                        $returnData[$key]['highlight'] = $item['highlight'];
                    }
                }
            }
        }
        $total = ArrayHelper::getValue($result, 'hits.total.value', 0);
        return [
            'list'  => $returnData,
            'total' => $total,
        ];
    }

    /**
     * 只获取列表
     * @return mixed
     * @throws NotSupportedException
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/27
     */
    public function all()
    {
        $result = $this->search();
        return ArrayHelper::getValue($result, 'list', []);
    }

    /**
     * 只计算数量
     * @return mixed
     * @throws NotSupportedException
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/27
     */
    public function count()
    {
        $result = $this->limit(0)->search();
        return ArrayHelper::getValue($result, 'count', 0);
    }

    /**
     * 获取一条
     * @return mixed
     * @throws NotSupportedException
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/28
     */
    public function one()
    {
        $result = $this->limit(1)->search();
        $list   = ArrayHelper::getValue($result, 'list', []);
        $record = reset($list);
        return $record;
    }

    /**
     * @param $id
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/28
     */
    public function findOne($id)
    {
        if (!$this->exists($id)) {
            return [];
        }
        $params = [
            'index' => $this->index,
            'type'  => $this->type,
            'id'    => $id,
        ];
        if ($this->routing) {
            $params['routing'] = $this->routing;
        }
        $response = $this->client->get($params);
        $response = ArrayHelper::getValue($response, '_source', []);
        return $response;
    }

    /**
     * @param $id
     * @return bool
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/28
     */
    public function exists($id)
    {
        $params = [
            'index' => $this->index,
            'type'  => $this->type,
            'id'    => $id,
        ];
        if ($this->routing) {
            $params['routing'] = $this->routing;
        }
        $response = $this->client->exists($params);
        return $response;
    }

    /**
     * 保存文档
     * @param $id
     * @param $data
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/28
     */
    public function saveDocument($id, $data)
    {
        if ($this->exists($id)) {
            return $this->editDocument($id, $data);
        } else {
            return $this->createDocument($id, $data);
        }
    }

    /**
     * 添加文档
     * @param $id
     * @param $data
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/28
     */
    public function createDocument($id, $data)
    {
        $params = [
            'index' => $this->index,
            'type'  => $this->type,
            'id'    => $id,
            'body'  => $data,
        ];
        if ($this->routing) {
            $params['routing'] = $this->routing;
        }

        $response = $this->client->index($params);
        return $response;
    }

    /**
     * 修改文档
     * @param $id
     * @param $data
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/28
     */
    public function editDocument($id, $data)
    {
        $params = [
            'index' => $this->index,
            'type'  => $this->type,
            'id'    => $id,
            'body'  => [
                'doc' => $data,
            ],
        ];

        if ($this->routing) {
            $params['routing'] = $this->routing;
        }
        $response = $this->client->update($params);
        return $response;
    }

    /**
     * 删除文档
     * @param $id
     * @return array|bool|callable
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/28
     */
    public function deleteDocument($id)
    {
        if (!$this->exists($id)) {
            return false;
        }
        $params = [
            'index' => $this->index,
            'id'    => $id,
        ];
        if ($this->routing) {
            $params['routing'] = $this->routing;
        }
        $response = $this->client->delete($params);
        return $response;
    }

    /**
     * @param $query
     * @return $this
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/27
     */
    public function query($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @param $limit
     * @return $this
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/27
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param $offset
     * @return $this
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/27
     */
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param $columns
     * @return $this
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/27
     */
    public function orderBy($columns)
    {
        $this->orderBy = $this->normalizeOrderBy($columns);
        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/27
     */
    public function asArray($value = true)
    {
        $this->asArray = $value;
        return $this;
    }

    /**
     * @param $condition
     * @return $this
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/28
     */
    public function where($condition)
    {
        $this->where = $condition;
        return $this;
    }

    /**
     * @param $condition
     * @return $this
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/28
     */
    public function andWhere($condition)
    {
        if ($this->where === null) {
            $this->where = $condition;
        } else {
            $this->where = ['and', $this->where, $condition];
        }

        return $this;
    }

    /**
     * @param $columns
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/27
     */
    protected function normalizeOrderBy($columns)
    {
        if ($columns instanceof ExpressionInterface) {
            return [$columns];
        } elseif (is_array($columns)) {
            return $columns;
        }

        $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
        $result  = [];
        foreach ($columns as $column) {
            if (preg_match('/^(.*?)\s+(asc|desc)$/i', $column, $matches)) {
                $result[$matches[1]] = strcasecmp($matches[2], 'desc') ? SORT_ASC : SORT_DESC;
            } else {
                $result[$column] = SORT_ASC;
            }
        }

        return $result;
    }

    public function buildOrderBy($columns)
    {
        if (empty($columns)) {
            return [];
        }
        $orders = [];
        foreach ($columns as $name => $direction) {
            if (is_string($direction)) {
                $column    = $direction;
                $direction = SORT_ASC;
            } else {
                $column = $name;
            }
            if ($column == '_id') {
                $column = '_uid';
            }

            if (is_array($direction)) {
                $orders[] = [$column => $direction];
            } else {
                $orders[] = [$column => ($direction === SORT_DESC ? 'desc' : 'asc')];
            }
        }

        return $orders;
    }

    /**
     * @param $condition
     * @return array|null
     * @throws NotSupportedException
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/28
     */
    public function buildQueryFromWhere($condition)
    {
        $where = $this->buildCondition($condition);
        if ($where) {
            $query = [
                'constant_score' => [
                    'filter' => $where,
                ],
            ];
            return $query;
        } else {
            return null;
        }
    }

    /**
     * @param $condition
     * @return array
     * @throws NotSupportedException
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/28
     */
    public function buildCondition($condition)
    {
        static $builders = [
            'not'         => 'buildNotCondition',
            'and'         => 'buildBoolCondition',
            'or'          => 'buildBoolCondition',
            'between'     => 'buildBetweenCondition',
            'not between' => 'buildBetweenCondition',
            'in'          => 'buildInCondition',
            'not in'      => 'buildInCondition',
            'like'        => 'buildLikeCondition',
            'not like'    => 'buildLikeCondition',
            'or like'     => 'buildLikeCondition',
            'or not like' => 'buildLikeCondition',
            'lt'          => 'buildHalfBoundedRangeCondition',
            '<'           => 'buildHalfBoundedRangeCondition',
            'lte'         => 'buildHalfBoundedRangeCondition',
            '<='          => 'buildHalfBoundedRangeCondition',
            'gt'          => 'buildHalfBoundedRangeCondition',
            '>'           => 'buildHalfBoundedRangeCondition',
            'gte'         => 'buildHalfBoundedRangeCondition',
            '>='          => 'buildHalfBoundedRangeCondition',
        ];

        if (empty($condition)) {
            return [];
        }
        if (!is_array($condition)) {
            throw new NotSupportedException('String conditions in where() are not supported by elasticsearch.');
        }
        if (isset($condition[0])) {
            // operator format: operator, operand 1, operand 2, ...
            $operator = strtolower($condition[0]);
            if (isset($builders[$operator])) {
                $method = $builders[$operator];
                array_shift($condition);

                return $this->$method($operator, $condition);
            } else {
                throw new InvalidParamException('Found unknown operator in query: ' . $operator);
            }
        } else {
            // hash format: 'column1' => 'value1', 'column2' => 'value2', ...

            return $this->buildHashCondition($condition);
        }
    }

    /**
     * @param $condition
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/5/28
     */
    private function buildHashCondition($condition)
    {
        $parts = $emptyFields = [];
        foreach ($condition as $attribute => $value) {
            if ($attribute == '_id') {
                if ($value === null) { // there is no null pk
                    $parts[] = ['terms' => ['_uid' => []]]; // this condition is equal to WHERE false
                } else {
                    $parts[] = ['ids' => ['values' => is_array($value) ? $value : [$value]]];
                }
            } else {
                if (is_array($value)) {
                    // IN condition
                    $parts[] = ['terms' => [$attribute => $value]];
                } else {
                    if ($value === null) {
                        $emptyFields[] = ['exists' => ['field' => $attribute]];
                    } else {
                        $parts[] = ['term' => [$attribute => $value]];
                    }
                }
            }
        }

        $query = ['must' => $parts];
        if ($emptyFields) {
            $query['must_not'] = $emptyFields;
        }
        return ['bool' => $query];
    }

    private function buildNotCondition($operator, $operands)
    {
        if (count($operands) != 1) {
            throw new InvalidParamException("Operator '$operator' requires exactly one operand.");
        }

        $operand = reset($operands);
        if (is_array($operand)) {
            $operand = $this->buildCondition($operand);
        }

        return [
            'bool' => [
                'must_not' => $operand,
            ],
        ];
    }

    private function buildBoolCondition($operator, $operands)
    {
        $parts = [];
        if ($operator === 'and') {
            $clause = 'must';
        } else if ($operator === 'or') {
            $clause = 'should';
        } else {
            throw new InvalidParamException("Operator should be 'or' or 'and'");
        }

        foreach ($operands as $operand) {
            if (is_array($operand)) {
                $operand = $this->buildCondition($operand);
            }
            if (!empty($operand)) {
                $parts[] = $operand;
            }
        }
        if ($parts) {
            return [
                'bool' => [
                    $clause => $parts,
                ],
            ];
        } else {
            return null;
        }
    }

    private function buildBetweenCondition($operator, $operands)
    {
        if (!isset($operands[0], $operands[1], $operands[2])) {
            throw new InvalidParamException("Operator '$operator' requires three operands.");
        }

        list($column, $value1, $value2) = $operands;
        if ($column === '_id') {
            throw new NotSupportedException('Between condition is not supported for the _id field.');
        }
        $filter = ['range' => [$column => ['gte' => $value1, 'lte' => $value2]]];
        if ($operator === 'not between') {
            $filter = ['bool' => ['must_not' => $filter]];
        }

        return $filter;
    }

    private function buildInCondition($operator, $operands)
    {
        if (!isset($operands[0], $operands[1]) || !is_array($operands)) {
            throw new InvalidParamException("Operator '$operator' requires array of two operands: column and values");
        }

        list($column, $values) = $operands;

        $values = (array) $values;

        if (empty($values) || $column === []) {
            return $operator === 'in' ? ['terms' => ['_uid' => []]] : []; // this condition is equal to WHERE false
        }

        if (is_array($column)) {
            if (count($column) > 1) {
                return $this->buildCompositeInCondition($operator, $column, $values);
            }
            $column = reset($column);
        }
        $canBeNull = false;
        foreach ($values as $i => $value) {
            if (is_array($value)) {
                $values[$i] = $value = isset($value[$column]) ? $value[$column] : null;
            }
            if ($value === null) {
                $canBeNull = true;
                unset($values[$i]);
            }
        }
        if ($column === '_id') {
            if (empty($values) && $canBeNull) { // there is no null pk
                $filter = ['terms' => ['_uid' => []]]; // this condition is equal to WHERE false
            } else {
                $filter = ['ids' => ['values' => array_values($values)]];
                if ($canBeNull) {
                    $filter = [
                        'bool' => [
                            'should' => [
                                $filter,
                                'bool' => ['must_not' => ['exists' => ['field' => $column]]],
                            ],
                        ],
                    ];
                }
            }
        } else {
            if (empty($values) && $canBeNull) {
                $filter = [
                    'bool' => [
                        'must_not' => [
                            'exists' => ['field' => $column],
                        ],
                    ],
                ];
            } else {
                $filter = ['terms' => [$column => array_values($values)]];
                if ($canBeNull) {
                    $filter = [
                        'bool' => [
                            'should' => [
                                $filter,
                                'bool' => ['must_not' => ['exists' => ['field' => $column]]],
                            ],
                        ],
                    ];
                }
            }
        }

        if ($operator === 'not in') {
            $filter = [
                'bool' => [
                    'must_not' => $filter,
                ],
            ];
        }

        return $filter;
    }

    /**
     * Builds a half-bounded range condition
     * (for "gt", ">", "gte", ">=", "lt", "<", "lte", "<=" operators)
     * @param string $operator
     * @param array $operands
     * @return array Filter expression
     */
    private function buildHalfBoundedRangeCondition($operator, $operands)
    {
        if (!isset($operands[0], $operands[1])) {
            throw new InvalidParamException("Operator '$operator' requires two operands.");
        }

        list($column, $value) = $operands;
        if ($column === '_id') {
            $column = '_uid';
        }

        $range_operator = null;

        if (in_array($operator, ['gte', '>='])) {
            $range_operator = 'gte';
        } elseif (in_array($operator, ['lte', '<='])) {
            $range_operator = 'lte';
        } elseif (in_array($operator, ['gt', '>'])) {
            $range_operator = 'gt';
        } elseif (in_array($operator, ['lt', '<'])) {
            $range_operator = 'lt';
        }

        if ($range_operator === null) {
            throw new InvalidParamException("Operator '$operator' is not implemented.");
        }

        $filter = [
            'range' => [
                $column => [
                    $range_operator => $value,
                ],
            ],
        ];

        return $filter;
    }

    protected function buildCompositeInCondition($operator, $columns, $values)
    {
        throw new NotSupportedException('composite in is not supported by elasticsearch.');
    }

    private function buildLikeCondition($operator, $operands)
    {
        throw new NotSupportedException('like conditions are not supported by elasticsearch.');
    }

    /**
     * 刷新索引
     * @param $params
     */
    public function refresh($params)
    {
        return $this->client->indices()->refresh($params);
    }

    /**
     * 批量更新
     * @param $updateData
     * @param $where
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/6/2
     * @return array
     */
    public function updateAll($updateData, $where)
    {
        $whereQuery = $this->buildQueryFromWhere($where);
        $params     = [
            'index' => $this->index,
            'type'  => $this->type,
            'body'  => [
                "query" => $whereQuery,
            ],
        ];
        if (!empty($updateData)) {
            $lineWhere = '';
            foreach ($updateData as $key => $item) {
                $lineWhere .= "ctx._source['" . $key . "'] = '" . $item . "';";
            }
            $params['body']['script']['inline'] = $lineWhere;
        }
        return $this->client->updateByQuery($params);
    }

    /**
     * 批量更新
     * @param $where
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/6/2
     * @return array
     */
    public function deleteAll($where)
    {
        $whereQuery = $this->buildQueryFromWhere($where);
        $params     = [
            'index' => $this->index,
            'type'  => $this->type,
            'body'  => [
                "query" => $whereQuery,
            ],
        ];
        return $this->client->deleteByQuery($params);
    }

    public function searchNew()
    {
        $query = [];
        $whereQuery = $this->buildQueryFromWhere($this->where);
        if ($whereQuery) {
            $query = $whereQuery;
        } else if ($this->query) {
            $query = $this->query;
        }
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'body' => [
                'size' => $this->limit,
                'from' => $this->offset
            ]
        ];

        if (!empty($query)) {
            $params['body']['query'] = $query;
        }

        $sort = $this->buildOrderBy($this->orderBy);
        if (!empty($sort)) {
            $params['body']['sort'] = $sort;
        }

        $response = $this->client->search($params);
        $list = ArrayHelper::getValue($response, 'hits.hits', []);
        $count = ArrayHelper::getValue($response, 'hits.total.value', 0);

        if (!$this->asArray && !empty($list)) {
            $list = array_column($list, '_source');
        }

        return ['list' => $list, 'count' => $count];
    }

}
