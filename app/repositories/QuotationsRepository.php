<?php

namespace App\Repositories;

use App\Models\Quotation_item;
use App\Models\Quotations;
use App\Models\Items;

class QuotationsRepository
{
    static public function getAllQuotations()
    {
        return Quotations::all();
    }

    static public function getQuotationById($id)
    {
        return Quotations::find($id);
    }

    static public function createQuotation(array $data)
    {
        $result = Quotations::create($data);

        return $result;
    }

    static public function addItemToQuotation($quotationId, array $itemData)
    {
        $quotation = Quotations::find($quotationId);
        if ($quotation) {
            $quotationSubtotal = 0;
            $quotationIva = 0;
            $quotationIgtf = 0;
            $quotationTotal = 0;

            foreach ($itemData as $item) {
                $itemModel = Items::find($item['id']);
                $price = $itemModel['base_price'] * $item['quantity'];
                $iva = $price * 0.16;
                $total = $price + $iva;

                $quotationSubtotal += (float)$price;
                $quotationIva += $iva;
                $quotationTotal += $total;

                Quotation_item::create([
                    'quotation_id' => $quotationId,
                    'item_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price_per_unit' => 0,
                    'iva' => $iva,
                    'subtotal' => $price,
                    'total' => $total
                ]);
            }
        }





        /*  echo count($testQuotations->getBindings()) . "bindings"; */
        Quotations::query()
            ->where('id', $quotationId)
            ->update([
                'subtotal' => $quotationSubtotal,
                'iva' => $quotationIva,
                'igtf' => $quotationIgtf,
                'total' => $quotationTotal
            ]);

        return true;
    }

    /* 

   

    public function getItemsByQuotationId($quotationId)
    {
        return Quotation_item::where('quotation_id', $quotationId)->get();
    } */

    public static function approveQuotation($id)
    {
        $quotation = Quotations::find($id);
        if ($quotation) {
            $result = Quotations::query()
                ->where('id', $id)
                ->update(['status' => 'approved']);
            return $result;
        }
        return null;
    }

    public static function deleteQuotation($id)
    {
        $quotation = QuotationsRepository::getQuotationById($id);
        if (!$quotation) {
            return false;
        }
        $result = Quotations::query()
            ->where('id', $id)
            ->delete();
        return $result ? true : false;
    }


    public static function updateQuotation($id, array $data)
    {
        $validatedFields = array_intersect_key($data, array_flip(Quotations::$fillable));
        $quotation = Quotations::find($id);
        if ($quotation) {
            Quotations::query()
                ->where('id', $id)
                ->update($data);
            return $quotation;
        }
        return null;
    }
}
