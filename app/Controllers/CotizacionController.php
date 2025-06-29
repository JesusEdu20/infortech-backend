<?php

use App\Models\SolicitudServicio;
use App\Models\Cotizacion;

class CotizacionController
{

    public function solicitar()
    {

        $input = json_decode(file_get_contents('php://input'), true);

        // 2. Validar datos (puedes añadir una lógica de validación más robusta aquí)
        if (empty($input['nombre_lead']) || empty($input['email_lead'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Nombre y email son requeridos.']);
            return;
        }

        // 3. Crear la solicitud de servicio
        try {
            SolicitudServicio::create([
                'nombre_lead' => $input['nombre_lead'],
                'email_lead' => $input['email_lead'],
                'telefono_lead' => $input['telefono_lead'] ?? null,
                'empresa_lead' => $input['empresa_lead'] ?? null,
                'descripcion_proyecto' => $input['descripcion_proyecto'] ?? null,
            ]);

            http_response_code(201); // Created
            echo json_encode(['mensaje' => 'Solicitud recibida correctamente. Nos pondremos en contacto pronto.']);
        } catch (\Exception $e) {
            http_response_code(500); // Internal Server Error
            // Idealmente, aquí registrarías el error en un log en lugar de mostrarlo
            echo json_encode(['error' => 'Error al guardar la solicitud: ' . $e->getMessage()]);
        }
    }

    /**
     * Genera y guarda la cotización detallada (para uso del administrador).
     */
    public function generarCotizacion()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        // Aquí recibes los datos detallados del formulario de cotización del administrador
        // por ejemplo: $input['solicitud_id'], $input['cantidad_camaras_ip'], etc.

        try {
            // Calcula los costos
            $calculos = $this->calcularCostos($input);

            // Prepara los datos para guardar en la BD
            $datosCotizacion = array_merge($input, $calculos);

            echo json_encode($input);
            // Crea la cotización
            Cotizacion::create($datosCotizacion);

            // Actualiza el estado de la solicitud original
            SolicitudServicio::query()
                ->where('id', '=', $input['solicitud_id'])
                ->update(['estado' => 'Cotizado']);


            http_response_code(201);
            echo json_encode(['mensaje' => 'Cotización creada exitosamente', 'data' => $datosCotizacion]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al generar la cotización: ' . $e->getMessage()]);
        }
    }

    /**
     * Lógica de negocio para calcular todos los costos basados en las reglas del PDF.
     * @param array $data Los datos de entrada para la cotización.
     * @return array Un array con {subtotal, monto_viaticos, monto_recargo_nocturno, monto_total}.
     */
    private function calcularCostos(array $data): array
    {
        $subtotal = 0;

        // 1. Costo por punto de cámara
        $subtotal += ($data['cantidad_camaras_analogicas'] ?? 0) * 15;
        $subtotal += ($data['cantidad_camaras_ip'] ?? 0) * 25;

        // 2. Costo de Cableado
        $metrosCable = $data['distancia_cableado_metros'] ?? 0;
        if ($metrosCable > 0) {
            if ($metrosCable > 305) {
                $subtotal += $metrosCable * 0.50;
            } else {
                $subtotal += $metrosCable * 1.00;
            }
        }

        // 3. Montaje de Rack (si hay cámaras IP)
        if (($data['cantidad_camaras_ip'] ?? 0) > 0) {
            if (($data['puertos_rack'] ?? 0) >= 48) {
                $subtotal += 300;
            } elseif (($data['puertos_rack'] ?? 0) >= 24) {
                $subtotal += 150;
            }
        }

        // 4. Configuración
        $subtotal += 100; // Costo fijo por configuración de equipos

        // 5. Costo por riesgo / equipos especializados
        $subtotal += $data['costo_equipos_especializados'] ?? 0;

        // --- CÁLCULO DE COSTOS ADICIONALES ---

        // 6. Viáticos y gastos operativos
        $monto_viaticos = 0;
        if ($data['es_fuera_del_estado'] ?? false) {
            $monto_viaticos += 250; // Gastos operativos fijos

            $dias = $data['dias_ejecucion_estimados'] ?? 0;
            $trabajadores = $data['numero_trabajadores'] ?? 3;
            $monto_viaticos += ($dias * $trabajadores * 160); // Viático por día por trabajador
        }

        // 7. Recargo por trabajo nocturno
        $monto_recargo_nocturno = 0;
        if ($data['trabajo_nocturno'] ?? false) {
            $monto_recargo_nocturno = $subtotal * 0.30;
        }

        // 8. Cálculo Final
        $monto_total = $subtotal + $monto_viaticos + $monto_recargo_nocturno;

        return [
            'subtotal' => $subtotal,
            'monto_viaticos' => $monto_viaticos,
            'monto_recargo_nocturno' => $monto_recargo_nocturno,
            'monto_total' => $monto_total
        ];
    }
}
