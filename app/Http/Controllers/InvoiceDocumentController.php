<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceDocumentController extends Controller
{
    public function showDocuments($id)
    {
        $invoice = Invoice::findOrFail($id);
        $documents = $invoice->getMedia('invoices');

        if ($documents->isEmpty()) {
            abort(404, 'Nenhum documento encontrado');
        }

        // Usar view em uma pasta diferente
        return view('filament.components.invoice-documents', [
            'invoice' => $invoice,
            'documents' => $documents,
        ]);
    }
}