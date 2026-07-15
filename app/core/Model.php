<?php
/**
 * 模型基类
 * 所有模型类都应继承此类
 */
abstract class Model {
    protected $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * 执行SQL查询
     */
    protected function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * 获取单行数据
     */
    protected function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取多行数据
     */
    protected function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取单个值
     */
    protected function fetchColumn($sql, $params = []) {
        return $this->query($sql, $params)->fetchColumn();
    }
    
    /**
     * 插入数据
     */
    protected function insert($table, $data) {
        $fields = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        return $stmt->execute() ? $this->pdo->lastInsertId() : false;
    }
    
    /**
     * 更新数据
     */
    protected function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE {$where}";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        foreach ($whereParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        return $stmt->execute();
    }
    
    /**
     * 删除数据
     */
    protected function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params)->rowCount() > 0;
    }
}
