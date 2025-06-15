<?php
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
}
