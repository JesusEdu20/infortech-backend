<?php

namespace App\Models;

use App\Database\QueryBuilder;

abstract class Model
{

    protected static string $table;
    protected static function newQuery(): QueryBuilder
    {
        if (empty(static::$table)) {
            throw new \Exception("La propiedad \$table no está definida para el modelo " . static::class);
        }
        return (new QueryBuilder())->table(static::$table);
    }


    public static function query(): QueryBuilder
    {
        return static::newQuery();
    }

    public static function all(): array
    {
        return static::newQuery()->get();
    }


    public static function find(mixed $id, array $columns = ['*']): ?array
    {
        return static::newQuery()->find($id, $columns);
    }

    public static function create(array $data): int
    {

        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        $result = static::newQuery()->insert($data);
        /* echo $result; */
        return $result;
    }
}
