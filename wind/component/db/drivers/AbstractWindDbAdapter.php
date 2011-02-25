<?php
/**
 * @author Qian Su <aoxue.1988.su.qian@163.com> 2010-11-11
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2110 phpwind.com
 * @license 
 */
L::import('WIND:component.exception.WindSqlException');
L::import('WIND:component.db.drivers.IWindDbConfig');
/**
 * the last known user to change this file in the repository  <$LastChangedBy$>
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @version $Id$ 
 * @package 
 */
abstract class AbstractWindDbAdapter {
	
	/**
	 * @var string 前次执行的sqly语句
	 */
	protected $last_sql = '';
	/**
	 * @var string 前句执行sql时的错误字符串
	 */
	protected $last_errstr = '';
	/**
	 * @var int 前句执行sql时的错误代码
	 */
	protected $last_errcode = 0;
	/**
	 * @var int 事务记数器
	 */
	protected $transCounter = 0;
	/**
	 * @var int 是否启用事务
	 */
	protected $enableSavePoint = 0;
	/**
	 * @var array 事务回滚点
	 */
	protected $savepoint = array();
	/**
	 * @var resoruce 数据库连接
	 */
	protected $connection = null;
	/**
	 * @var WindSqlBuilder sql语句生成器
	 */
	protected $sqlBuilder = null;
	/**
	 * @var resource 当前查询句柄
	 */
	protected $query = null;
	/**
	 * @var array 数据库连接配置
	 */
	protected $config = array();
	
	/**
	 * @var array 自身的数据库驱动配置信息
	 */
	protected $driver = array();
	/**
	 * @var array 自身的sql语句组装器信息
	 */
	protected $builder =  array();
	/**
	 * 初始化配置
	 * @param array $config
	 * @param array $driver
	 * @param array $builder
	 */
	public function __construct(array $config,array $driver = array(),array $builder = array()) {
		$this->parseConfig($config);
		$this->connect();
		$this->driver = $driver;
		$this->builder = $builder;
	}
	
	/**
	 * 解析数数库配置
	 * @param array $config 数据库配置
	 * @return array 返回解析后的数据库配置
	 */
	final protected function parseConfig(array $config) {
		$defautConfig = array(
			IWindDbConfig::PCONNECT => false,
			IWindDbConfig::FORCE => false,
			IWindDbConfig::CHARSET =>'gbk',
			
		);
		$this->config = array_merge($defautConfig,$config);
	}
	
	/**
	 * 连接数据库
	 */
	protected abstract function connect();
	/**
	 * 执行相关sql语句操作
	 * @param string $sql sql语句
	 * @return boolean;
	 */
	public abstract function query($sql);
	/**
	 * 取得查询的所有结果集
	 * @param int $fetch_type 提取结果集类型
	 * @return array
	 */
	public abstract function getAllRow($fetch_type = IWindDbConfig::ASSOC);
	
	/**
	 * 取得查询的单条结果集
	 * @param int $fetch_type 提取结果集类型
	 * @return array
	 */
	public abstract function getRow($fetch_type = IWindDbConfig::ASSOC);
	/**
	 * 开始事务点
	 */
	public abstract function beginTrans();
	/**
	 * 提交事务
	 */
	public abstract function commitTrans();
	/**
	 * 关闭数据库
	 */
	public abstract function close();
	/**
	 * 取得所执行的sql语句影响行数
	 */
	public abstract function getAffectedRows();
	/**
	 * 取得最后数据库操作的自增ID
	 */
	public abstract function getLastInsertId();
	
	/**
	 * 取得指定数据库的元数据表
	 * @param string $schema 数据库
	 */
	public abstract function getMetaTables($schema = '');
	
	/**
	 *取得指定数据表的元数据列 
	 */
	public abstract function getMetaColumns($table);
	/**
	 * 释放数据库连接资源
	 */
	public abstract function dispose();
	/**
	 * 数据库操作操作错误处理
	 * @param string $sql 执行的sql语句
	 */
	protected abstract function error($sql);
	
	/**
	 * 返回sqlBuilder生成器
	 * @param array builderConfig 生成器配置
	 * @return WindSqlBuilder
	 * @todo 重构获取sqlbuilder方式
	 */
	final public function getSqlBuilder($builderConfig = array()) {
		if (empty($this->sqlBuilder)) {
			$builderConfig = $builderConfig ? $builderConfig : $this->builder;
			$builderClass = $builderConfig[IWindDbConfig::CLASSNAME];
			$class = L::import($builderClass);
			if (false === class_exists($class)) {
				throw new WindSqlException($class, WindSqlException::DB_BUILDER_NOT_EXIST);
			}
			$this->sqlBuilder = new $class($this);
		}
		return $this->sqlBuilder;
	}
	/**
	 * 执行添加数据操作 (insert)
	 * @param string  $sql 新增sql语句
	 * @return boolean
	 */
	final public function insert($sql) {
		return $this->query($sql);
	}
	
	/**
	 * 执行更新数据操作
	 * @param string  $sql 更新sql语句
	 * @return boolean
	 */
	final public function update($sql) {
		return $this->query($sql);
	}
	/**
	 * 执行查询数据操作
	 * @param string  $sql 查询sql语句
	 * @return boolean
	 */
	final public function select($sql) {
		return $this->query($sql);
	}
	/**
	 * 执行删除数据操作
	 * @param string  $sql 删除sql语句
	 * @return boolean
	 */
	final public function delete($sql) {
		return $this->query($sql);
	}
	
	/**
	 * 执行更新与添加数据操作
	 * @param string  $sql 替换sql语句
	 * @return boolean
	 */
	final public function replace($sql) {
		return $this->query($sql);
	}
	
	/**
	 * 返回DataBase连接
	 * @return resoruce
	 */
	public function getConnection() {
		return $this->connection;
	}
	
	/**
	 * 返回数据库配置
	 * @return array
	 */
	public function getConfig() {
		return $this->config;
	}
	
	/**
	 * 返回上一条sqly语句
	 * @return string
	 */
	final public function getLastSql() {
		return $this->last_sql;
	}
	
	/**
	 * 取得数据库
	 * @return string:
	 */
	final public function getSchema() {
		return $this->config[IWindDbConfig::NAME];
	}
	
	/**
	 * 取得数据库驱动
	 * @return string;
	 */
	final public function getDriver() {
		return $this->config[IWindDbConfig::DRIVER];
	}
	
	public function __destruct() {
		$this->dispose();
	}
}