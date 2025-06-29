<?php

use App\Database\Database;
use App\Database\QueryBuilder;
use App\Models\User;
use App\Models\Quotation_item;
use App\Models\Quotations;
use App\Models\Model;
use App\Repositories\QuotationsRepository;



class QuotationsController
{
    public function createQuotation()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $fillable = [
            'description',
            'subTotal',
            'iva',
            'igtf',
            'total',
            'items'
        ];


        if (
            empty($data['description'])
        ) {

            http_response_code(400);
            echo json_encode(['message' => 'All fields are required']);
            return;
        }
        /* Validaciones */
        /*  if (!is_numeric($data['subTotal']) || !is_numeric($data['iva']) || !is_numeric($data['igtf']) || !is_numeric($data['total'])) {
            http_response_code(400);
            echo json_encode(['message' => 'SubTotal, IVA, IGTF and Total must be numeric']);
            return;
        } */

        /* if (
            $data['subTotal'] < 0
            || $data['iva'] < 0
            || $data['igtf'] < 0
            || $data['total'] < 0
        ) {
            http_response_code(400);
            echo json_encode(['message' => 'SubTotal, IVA, IGTF and Total must be non-negative numbers']);
            return;
        } */

        if (!is_string($data['description'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Description must be a string']);
            return;
        }

        if (!isset($data['items']) || !is_array($data['items']) || count($data['items']) === 0) {
            http_response_code(400);
            echo json_encode(['message' => 'Items field is required']);
            return;
        }
        foreach ($data['items'] as $item) {
            if (!isset($item['id']) || !isset($item['quantity'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Each item must have an id and quantity']);
                return;
            }
            if (!is_numeric($item['id']) || !is_numeric($item['quantity'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Item id and quantity must be numeric']);
                return;
            }
        }


        $validatedFields = array_intersect_key($data, array_flip($fillable));

        $quotationId = QuotationsRepository::createQuotation([
            'description' => $validatedFields['description'],
            'subtotal' => 0,
            'iva' => 0,
            'igtf' => 0,
            'total' => 0,
            'user_id' => 1
        ]);
        /* echo $quotationId; */
        QuotationsRepository::addItemToQuotation(
            $quotationId,
            $validatedFields['items'] ?? []
        );

        http_response_code(201);
        echo json_encode(['message' => 'Quotation created successfully']);
    }

    public function getQuotations()
    {
        $quotations = QuotationsRepository::getAllQuotations();
        http_response_code(200);
        echo json_encode($quotations);
    }

    public function getQuotationById($params)
    {
        $id = $params['id'] ?? null;
        try {
            $quotation = QuotationsRepository::getQuotationById($id);
            if ($quotation) {
                http_response_code(200);
                echo json_encode($quotation);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Quotation not found']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Error fetching quotation: ' . $e->getMessage()]);
            return;
        }
    }

    public function approveQuotation($params)
    {
        $id = $params['id'] ?? null;

        try {
            $quotation = QuotationsRepository::getQuotationById($id);
            if ($quotation) {
                $result = QuotationsRepository::approveQuotation($id);

                if ($result) {
                    http_response_code(200);
                    echo json_encode(['message' => 'Quotation approved successfully']);
                } else {
                    http_response_code(400);
                    echo json_encode(['message' => 'Quotation could not be approved']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Quotation not found']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Error approving quotation: ' . $e->getMessage()]);
            return;
        }
    }


    public function deleteQuotation($params)
    {
        $id = $params['id'] ?? null;

        try {

            /*   if ($quotation) { */
            $result = QuotationsRepository::deleteQuotation($id);

            if ($result) {
                http_response_code(200);
                echo json_encode(['message' => 'Quotation deleted successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['message' => 'Quotation could not be deleted']);
            }

            /*   } */ /* else {
                http_response_code(404);
                echo json_encode(['message' => 'Quotation not found']);
            } */
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Error deleting quotation: ' . $e->getMessage()]);
            return;
        }
    }

    public function updateQuotation($params)
    {
        $id = $params['id'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);
        print_r($data);
        if (empty($data)) {
            http_response_code(400);
            echo json_encode(['message' => 'No data provided for update']);
            return;
        }

        try {
            $updatedQuotation = QuotationsRepository::updateQuotation($id, $data);
            if (!$updatedQuotation) {
                http_response_code(404);
                echo json_encode(['message' => 'Quotation not found or could not be updated']);
                return;
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Error updating quotation: ' . $e->getMessage()]);
            return;
        }
    }
}
