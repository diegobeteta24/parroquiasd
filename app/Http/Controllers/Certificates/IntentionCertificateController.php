<?php

namespace App\Http\Controllers\Certificates;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Controlador para la descarga de certificado genérico de intenciones de misa.
 * 
 * NOTA: Este certificado es genérico ya que en la página de éxito del pago
 * todavía no tenemos el ID de la intención (se crea de forma asíncrona vía webhook).
 * 
 * El certificado personalizado con datos específicos se genera desde el admin
 * en IntentionController@certificate cuando la secretaria lo descarga.
 */
class IntentionCertificateController extends Controller
{
    /**
     * Genera y descarga un certificado genérico de intención de misa en PDF.
     * 
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        // Generar PDF usando la vista de certificado genérico
        $pdf = Pdf::loadView('intentions.certificate-generic', [
            'emitida' => now()->translatedFormat('d \\d\\e F \\d\\e Y'),
            'parish' => 'Parroquia Santo Domingo — Ciudad de Guatemala',
        ])->setPaper('a4', 'landscape');

        $filename = 'Certificado-Intencion-' . now()->format('Ymd') . '.pdf';
        
        return $pdf->download($filename);
    }
}
