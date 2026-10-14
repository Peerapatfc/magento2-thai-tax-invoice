<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Model;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutFactory;
use Peerapatfc\ThaiTaxInvoice\ViewModel\InvoiceDocument;

/**
 * Renders an issued tax invoice as a standalone HTML page, the same for the admin and the customer.
 */
class InvoiceRenderer
{
    /**
     * @param LayoutFactory $layoutFactory
     * @param InvoiceDocument $viewModel
     */
    public function __construct(
        private readonly LayoutFactory $layoutFactory,
        private readonly InvoiceDocument $viewModel
    ) {
    }

    /**
     * The HTML of the invoice, as it was when it was issued.
     *
     * @param Invoice $invoice
     * @return string
     */
    public function render(Invoice $invoice): string
    {
        $document = $invoice->getDocument();
        $document['number'] = $document['number'] ?? $invoice->getInvoiceNumber();

        return $this->layoutFactory->create()
            ->createBlock(Template::class)
            ->setTemplate('Peerapatfc_ThaiTaxInvoice::invoice/print.phtml')
            ->setData('document', $document)
            ->setData('view_model', $this->viewModel)
            ->toHtml();
    }
}
