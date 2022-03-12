<?php

namespace App\Core\Database\QueryBuilder;

use PDO;
use PDOException;
use Exception;
use App\Core\App;
use App\Core\Filesystem\Filesystem;
use App\Core\Database\QueryBuilder\Exception\QueryBuilderException;
use RuntimeException;

class QueryBuilder implements QueryBuilderInterface
{
	/**
	 * The PDO instance.
	 *
	 * @var PDO
	 */
	protected $pdo;

	/**
	 * The actual result of the statement.
	 *
	 */
	private $result;

	/**
	 * The statement
	 *
	 */
	private $queryStatement;

	/**
	 * The pagiantion limit
	 *
	 */
	private $paginateLimit;

	/**
	 * to determine if it's selectLoop
	 *
	 */
	private $querytype;

	/**
	 * additional where params for with() method
	 *
	 */
	private $withFilter;

	/**
	 * will listen to all queries
	 *
	 */
	private $listen = [];

	/**
	 * Create a new QueryBuilder instance.
	 *
	 * @param PDO $pdo
	 */
	public function __construct($pdo)
	{
		$this->pdo = $pdo;
	}

	/**
	 * Select a record from a database table.
	 *
	 * @param string $columns
	 * @param string $table
	 * @param string $params
	 */
	public function select($columns, $table, $params = '')
	{
		try {
			$this->querytype = 'select';
			$inject = ($params == '') ? "" : "WHERE $params";
			$statement = $this->pdo->prepare("SELECT {$columns} FROM {$table} {$inject}");
			$statement->execute();
			$this->queryStatement = "SELECT {$columns} FROM {$table} {$inject}";
			$this->listen[] = "SELECT {$columns} FROM {$table} {$inject}";
			$this->result = $statement->fetch(PDO::FETCH_ASSOC);
			return $this;
		} catch (PDOException $e) {
			throw new QueryBuilderException($e->getMessage(), $e);
		}
	}

	/**
	 * Select all records from a database table.
	 *
	 * @param string $table
	 */
	public function selectLoop($column, $table, $params = '')
	{
		$inject = ($params == '') ? "" : "WHERE $params";
		$test = "SELECT {$column} FROM {$table} {$inject}";
		$this->querytype = "selectLoop";
		$this->queryStatement = $test;
		return $this;
	}

	/**
	 * GET the result of a query
	 * 
	 */
	public function get()
	{
		if ($this->querytype == "selectLoop") {
			try {
				$statement = $this->pdo->prepare("{$this->queryStatement}");
				$statement->execute();
				$this->listen[] = "{$this->queryStatement}";
				$this->result = $statement->fetchAll(PDO::FETCH_ASSOC);
				return $this->result;
			} catch (PDOException $e) {
				throw new QueryBuilderException($e->getMessage(), $e);
			}
		} else {
			return $this->result;
		}
	}

	/**
	 * this will solve n+1 problem
	 * will get the data of the foreign id in the current table
	 * 
	 */
	public function with($params = [])
	{
		if ($this->querytype == "selectLoop") {
			$currentTableDatas = DB()->query($this->queryStatement, 'Y')->get();
		} else {
			$currentTableDatas = $this->result;
		}

		$collectedIdFrom = [];
		foreach ($params as $relationTable => $param) {
			$foreignIdFromCurrentTable = [];
			foreach ($currentTableDatas as $key => $currentTableData) {
				if (is_array($currentTableData)) {
					$foreignIdFromCurrentTable[] = $currentTableData[$param[0]];
				} else {
					$foreignIdFromCurrentTable[] = $currentTableDatas[$param[0]];
				}
			}

			$collectedIdFrom[$relationTable] = $foreignIdFromCurrentTable;
		}

		$relationDatas = [];
		foreach ($params as $relationTable => $primaryColumn) {
			$relationPrimaryColumn = $primaryColumn[1];
			$implodedIds = implode("','", array_unique($collectedIdFrom[$relationTable]));

			$andFilter = (!empty($this->withFilter[$relationTable])) ? $this->withFilter[$relationTable] : '';

			$statement = $this->pdo->prepare("SELECT * FROM `{$relationTable}` WHERE `{$relationTable}`.`$relationPrimaryColumn` IN('$implodedIds') {$andFilter}");
			$statement->execute();

			$this->listen[] = "SELECT * FROM `{$relationTable}` WHERE `{$relationTable}`.`$relationPrimaryColumn` IN('$implodedIds') {$andFilter}";
			$relationDatas[$relationTable] = $statement->fetchAll(PDO::FETCH_ASSOC);
		}


		$newResultSet = [];
		foreach ($currentTableDatas as $currentTableData) {
			foreach ($params as $relationTable => $primaryColumn) {
				$countRelationData = count($relationDatas[$relationTable]);
				foreach ($relationDatas[$relationTable] as $key => $relationData) {
					if (is_array($currentTableData)) {
						if ($currentTableData[$primaryColumn[0]] == $relationData[$primaryColumn[1]]) {
							if ($countRelationData > 1) {
								$currentTableData[$relationTable][] = $relationData;
							} else {
								$currentTableData[$relationTable] = $relationData;
							}
						}
					} else {
						if ($currentTableDatas[$primaryColumn[0]] == $relationData[$primaryColumn[1]]) {
							if ($countRelationData > 1) {
								$currentTableDatas[$relationTable] = $relationDatas[$relationTable];
							} else {
								$currentTableDatas[$relationTable] = $relationData;
							}
						}
					}
				}
			}

			if (is_array($currentTableData)) {
				$newResultSet[] = $currentTableData;
			} else {
				$newResultSet = $currentTableDatas;
			}
		}


		$this->querytype = "with";
		$this->result = $newResultSet;
		return $this;
	}

	/**
	 * this will count rows in a foreign table
	 * 
	 */
	public function withCount($params = [])
	{
		if ($this->querytype == "selectLoop") {
			$currentTableDatas = DB()->query($this->queryStatement, 'Y')->get();
		} else {
			$currentTableDatas = $this->result;
		}

		$collectedIdFrom = [];
		foreach ($params as $relationTable => $param) {
			$foreignIdFromCurrentTable = [];
			foreach ($currentTableDatas as $key => $currentTableData) {
				if (is_array($currentTableData)) {
					$foreignIdFromCurrentTable[] = $currentTableData[$param[0]];
				} else {
					$foreignIdFromCurrentTable[] = $currentTableDatas[$param[0]];
				}
			}

			$collectedIdFrom[$relationTable] = $foreignIdFromCurrentTable;
		}

		$relationDatas = [];
		foreach ($params as $relationTable => $primaryColumn) {
			$relationPrimaryColumn = $primaryColumn[1];
			$implodedIds = implode("','", array_unique($collectedIdFrom[$relationTable]));

			$andFilter = (!empty($this->withFilter[$relationTable])) ? $this->withFilter[$relationTable] : '';

			$statement = $this->pdo->prepare("SELECT COUNT(*) as '{$relationTable}_count', `{$relationTable}`.`$relationPrimaryColumn` FROM `{$relationTable}`  WHERE `{$relationTable}`.`$relationPrimaryColumn` IN('$implodedIds') {$andFilter} GROUP BY `{$relationTable}`.`$relationPrimaryColumn`");
			$statement->execute();

			$this->listen[] = "SELECT COUNT(*) as '{$relationTable}_count', `{$relationTable}`.`$relationPrimaryColumn` FROM `{$relationTable}`  WHERE `{$relationTable}`.`$relationPrimaryColumn` IN('$implodedIds') {$andFilter} GROUP BY `{$relationTable}`.`$relationPrimaryColumn`";

			$relationDatas[$relationTable] = $statement->fetchAll(PDO::FETCH_ASSOC);
		}

		$newResultSet = [];
		foreach ($currentTableDatas as $currentTableData) {
			foreach ($params as $relationTable => $primaryColumn) {
				foreach ($relationDatas[$relationTable] as $relationData) {
					if ($currentTableData[$primaryColumn[0]] == $relationData[$primaryColumn[1]]) {
						$currentTableData[$relationTable . '_count'] = $relationData[$relationTable . '_count'];
					}
				}
			}

			$newResultSet[] = $currentTableData;
		}

		$this->querytype = "withCount";
		$this->result = $newResultSet;
		return $this;
	}

	/**
	 * add extra where params to the with() mwthod
	 * 
	 */
	public function andFilter($andFilter = [])
	{
		$this->withFilter = $andFilter;
		return $this;
	}

	/**
	 * Listens for all the database queries
	 * 
	 */
	public function listen()
	{
		return $this->listen;
	}

	/**
	 * insert record to a database table.
	 *
	 * @param string $table_name
	 * @param array $form_data
	 * @param string $last_id
	 */
	public function insert($table_name, $form_data, $last_id = 'N')
	{
		$fields = array_keys($form_data);

		$sql = "INSERT INTO " . $table_name . "(`" . implode('`,`', $fields) . "`) VALUES ('" . implode("','", $form_data) . "')";

		try {
			$statement = $this->pdo->prepare($sql);
			$statement->execute();

			$this->listen[] = "INSERT INTO " . $table_name . "(`" . implode('`,`', $fields) . "`) VALUES ('" . implode("','", $form_data) . "')";
			$lastID = $this->pdo->lastInsertId();
			if ($last_id == 'Y') {
				if ($statement) {
					return $lastID;
				} else {
					return 0;
				}
			} else {
				if ($statement) {
					return 1;
				} else {
					return 0;
				}
			}
		} catch (PDOException $e) {
			throw new QueryBuilderException($e->getMessage(), $e);
		}
	}

	/**
	 * update a record from a database table.
	 *
	 * @param string $table_name
	 * @param array $form_data
	 * @param string $where_clause
	 */
	public function update($table_name, $form_data, $where_clause = '')
	{
		$whereSQL = '';
		if (!empty($where_clause)) {
			if (substr(strtoupper(trim($where_clause)), 0, 5) != 'WHERE') {
				$whereSQL = " WHERE " . $where_clause;
			} else {
				$whereSQL = " " . trim($where_clause);
			}
		}
		$sql = "UPDATE " . $table_name . " SET ";
		$sets = array();
		foreach ($form_data as $column => $value) {
			$sets[] = "`" . $column . "` = '" . $value . "'";
		}
		$sql .= implode(', ', $sets);
		$sql .= $whereSQL;

		try {
			$statement = $this->pdo->prepare($sql);
			$statement->execute();

			$this->listen[] = $sql;

			if ($statement) {
				return 1;
			} else {
				return 0;
			}
		} catch (PDOException $e) {
			throw new QueryBuilderException($e->getMessage(), $e);
		}
	}

	/**
	 * delete a record from a database table.
	 *
	 * @param string $table_name
	 * @param string $where_clause
	 */
	public function delete($table_name, $where_clause = '')
	{
		$whereSQL = '';
		if (!empty($where_clause)) {
			if (substr(strtoupper(trim($where_clause)), 0, 5) != 'WHERE') {
				$whereSQL = " WHERE " . $where_clause;
			} else {
				$whereSQL = " " . trim($where_clause);
			}
		}

		$sql = "DELETE FROM " . $table_name . $whereSQL;

		try {
			$statement = $this->pdo->prepare($sql);
			$statement->execute();

			$this->listen[] = $sql;

			if ($statement) {
				return 1;
			} else {
				return 0;
			}
		} catch (PDOException $e) {
			throw new QueryBuilderException($e->getMessage(), $e);
		}
	}

	/**
	 * query a record from a database.
	 *
	 * @param string $query
	 * @param string $fetch (optional)
	 */
	public function query($query, $fetch = "N")
	{
		try {
			$statement = $this->pdo->prepare($query);
			$statement->execute();

			$this->listen[] = $query;

			if ($fetch == "Y") {
				$this->result = $statement->fetchAll(PDO::FETCH_ASSOC);
				return $this;
			} else {
				if ($statement) {
					return 1;
				} else {
					return 0;
				}
			}
		} catch (PDOException $e) {
			throw new QueryBuilderException($e->getMessage(), $e);
		}
	}

	/**
	 * seed a record/s into the database.
	 *
	 */
	public function seeder($table, $length, $tableColumns = [])
	{
		Filesystem::noMemoryLimit();
		$start_time = microtime(TRUE);

		$iterate = function ($tableColumns, $length) {
			for ($x = 0; $x < $length; $x++) {
				yield $tableColumns;
			}
		};

		foreach (iterator_to_array($iterate($tableColumns, $length)) as $customerInfo) {
			DB()->insert($table, $customerInfo);
		}

		$end_time = microtime(TRUE);
		$time_taken = ($end_time - $start_time);
		$time_taken = round($time_taken, 5);
		$memoryUsage = (round(memory_get_peak_usage() / 1024 / 1024));

		return "Success seed! Page generated in {$time_taken} seconds using {$memoryUsage}MB.";
	}

	/**
	 * paginate the query against the db
	 *
	 */
	public function paginate($per_page = '10')
	{
		$limit = isset($_SESSION['per_page']) ? $_SESSION['per_page'] : $per_page;
		$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
		$paginationStart = ($page - 1) * $limit;

		$this->paginateLimit = $limit;
		$this->paginateCurrentPage = $page;

		$this->queryStatement = $this->queryStatement . " LIMIT {$paginationStart}, {$limit}";
		return $this;
	}

	public function links()
	{
		$limit = $this->paginateLimit;
		$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;

		// Get total records
		$allRecrods = count(DB()->query($this->queryStatement, 'Y')->get());

		// Calculate total pages
		$total_pages = ($allRecrods == 1) ? 1 : ceil($allRecrods / $limit);

		// Prev + Next
		$prev = $page - 1;
		$next = $page + 1;

		$disabledPrev = ($page <= 1) ? 'disabled' : '';
		$disabledNext = ($page >= $total_pages) ? 'disabled' : '';

		$links = "";
		$links .= '<nav aria-label="Page navigation example mt-5">';
		$links .= '<ul class="pagination pagination-sm justify-content-center">';
		$links .= '<li class="page-item ' . $disabledPrev . '">';
		$links .= '<a class="page-link" href="' . "?page=" . $prev . '">Previous</a>';
		$links .= '</li>';
		if ($total_pages >= 1 && $page <= $total_pages) {

			if ($page == 1) {
				$active1 = 'active';
				$activeValue1 = 1 . ' <span class="sr-only">(current)</span>';
			} else {
				$active1 = '';
				$activeValue1 = 1;
			}

			$links .= '<li class="page-item ' . $active1 . '">';
			$links .= "<a class='page-link' href=\"?page=1\">{$activeValue1}</a>";
			$links .= '</li>';

			$i = max(2, $page - 3);

			if ($i > 3) {
				$links .= '<li class="page-item disabled">';
				$links .= '<a class="page-link" href="">...</a>';
				$links .= '</li>';
			}

			for (; $i < min($page + 3, $total_pages); $i++) {

				if ($page == $i) {
					$active = 'active';
					$activeValue = $i . ' <span class="sr-only">(current)</span>';
				} else {
					$active = '';
					$activeValue = $i;
				}

				$links .= '<li class="page-item ' . $active . '">';
				$links .= "<a class='page-link' href=\"?page={$i}\">{$activeValue}</a>";
				$links .= '</li>';
			}

			if ($i < $total_pages) {
				$links .= '<li class="page-item disabled">';
				$links .= '<a class="page-link" href="">...</a>';
				$links .= '</li>';

				$links .= "<a class='page-link' href=\"?page={$total_pages}\">{$total_pages}</a>";
			}
		}

		$nextlink = ($page >= $total_pages) ? '#' : "?page=" . $next;
		$links .= '<li class="page-item ' . $disabledNext . '">';
		$links .= '<a class="page-link" href="' . $nextlink . '">Next</a>';
		$links .= '</li>';

		$links .= '</li>';
		$links .= '</ul>';
		$links .= '</nav>';
		return $links;
	}

	public function linksToArray()
	{
		$limit = $this->paginateLimit;
		$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;

		// Get total records
		$allRecrods = count(DB()->query($this->queryStatement, 'Y')->get());

		// Calculate total pages
		$total_pages = ($allRecrods == 1) ? 1 : ceil($allRecrods / $limit);

		// Prev + Next
		$prev = $page - 1;
		$next = $page + 1;

		$disabledPrev = ($page <= 1) ? 'disabled' : '';
		$disabledNext = ($page >= $total_pages) ? 'disabled' : '';

		$data = [
			"total" => $allRecrods,
			"per_page" => $limit,
			"current_page" => $page,
			"last_page" => $total_pages,
			"first_page_url" => "?page=1",
			"last_page_url" => "?page=" . $total_pages,
			"next_page_url" => "?page=" . $next,
			"prev_page_url" => "?page=" . $prev,
			"path" => App::get('base_url'),
			"from" => $page,
			"to" => $total_pages,
			"data" => []
		];

		return $data;
	}
}
