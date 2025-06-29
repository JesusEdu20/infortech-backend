<?php

use App\Database\Database;
use App\Database\QueryBuilder;
use App\Models\User;
use App\Models\Model;

class UserController
{
    public function index()
    {
        echo json_encode(['usuarios' => ['Ana', 'Luis', 'Carlos']]);
    }

    public function store()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        echo json_encode(['mensaje' => 'Usuario creado', 'data' => $input]);
    }

    public function detail($params)
    {
        echo json_encode([
            'usuario_id' => $params['id'],
            'post_id' => $params['postId']
        ]);
    }
    public function more()
    {
        try {
            $users = User::query()->where('id', '=', 1)->get();

            print_r(json_encode($users));
        } catch (Exception $e) {
            echo "<p style='color:red;'>Error al seleccionar usuarios: " . $e->getMessage() . "</p>";
        }
    }
}
