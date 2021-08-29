<?php

namespace App\Core\Database\QueryBuilder;

interface QueryBuilderInterface
{
    /**
     * Select a record from a database table.
     *
     * @param string $columns
     * @param string $table
     * @param string $params
     */
    public function select($columns, $table, $params = '');

    /**
     * Select all records from a database table.
     *
     * @param string $table
     */
    public function selectLoop($column, $table, $params = '');

    /**
     * GET the result of a query
     * 
     */
    public function get();

    /**
     * this will solve n+1 problem
     * will get the data of the foreign id in the current table
     * 
     */
    public function with($params = []);

    /**
     * this will count rows in a foreign table
     * 
     */
    public function withCount($params = []);

    /**
     * add extra where params to the with() mwthod
     * 
     */
    public function andFilter($andFilter = []);

    /**
     * Listens for all the database queries
     * 
     */
    public function listen();

    /**
     * insert record to a database table.
     *
     * @param string $table_name
     * @param array $form_data
     * @param string $last_id
     */
    public function insert($table_name, $form_data, $last_id = 'N');

    /**
     * update a record from a database table.
     *
     * @param string $table_name
     * @param array $form_data
     * @param string $where_clause
     */
    public function update($table_name, $form_data, $where_clause = '');

    /**
     * delete a record from a database table.
     *
     * @param string $table_name
     * @param string $where_clause
     */
    public function delete($table_name, $where_clause = '');

    /**
     * query a record from a database.
     *
     * @param string $query
     * @param string $fetch (optional)
     */
    public function query($query, $fetch = "N");

    /**
     * seed a record/s into the database.
     *
     */
    public function seeder($table, $length, $tableColumns = []);
}
