<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo e($title); ?> <?php echo e($number); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page { size: A4; margin: 14mm; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #eef2f7; color: #0f172a; font-family: Arial, sans-serif; font-size: 12px; }
        .toolbar { max-width: 210mm; margin: 14px auto; display: flex; justify-content: flex-end; gap: 8px; }
        .btn { border: 1px solid #2563eb; color: #fff; background: #2563eb; padding: 8px 13px; border-radius: 4px; text-decoration: none; cursor: pointer; }
        .page { width: 210mm; min-height: 297mm; margin: 0 auto 18px; background: #fff; padding: 14mm; box-shadow: 0 8px 24px rgba(15, 23, 42, .12); }
        .header { display: flex; justify-content: space-between; gap: 24px; border-bottom: 2px solid #0f172a; padding-bottom: 14px; }
        .brand h1 { font-size: 21px; margin: 0 0 5px; }
        .brand p, .meta p, .party p { margin: 2px 0; color: #475569; }
        .doc-title { text-align: right; }
        .doc-title h2 { margin: 0 0 8px; font-size: 20px; text-transform: uppercase; }
        .badge { display: inline-block; padding: 4px 8px; background: #e0f2fe; color: #075985; border-radius: 3px; text-transform: uppercase; font-size: 10px; }
        .section { margin-top: 18px; }
        .party-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .box { border: 1px solid #cbd5e1; padding: 10px; min-height: 86px; }
        .box h3 { margin: 0 0 8px; font-size: 12px; text-transform: uppercase; color: #334155; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; text-align: left; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; }
        .text-end { text-align: right; }
        .totals { width: 74mm; margin-left: auto; margin-top: 14px; }
        .totals td { padding: 7px 8px; }
        .grand td { font-size: 14px; font-weight: 700; background: #f8fafc; }
        .footer { margin-top: 34px; display: flex; justify-content: space-between; gap: 18px; align-items: end; }
        .signature { width: 62mm; text-align: center; padding-top: 32px; border-top: 1px solid #0f172a; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .page { box-shadow: none; margin: 0; width: auto; min-height: auto; padding: 0; }
        }
    </style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"></head>
<body>
    <div class="toolbar">
        <button class="btn" onclick="window.print()">Print A4</button>
        <a class="btn" href="<?php echo e(route('admin.sales-documents.pdf', ['document' => request()->route('document'), 'id' => request()->route('id')])); ?>">Download PDF</a>
    </div>

    <main class="page">
        <div class="header">
            <div class="brand">
                <h1><?php echo e(config('admin.brand.name', 'Bawaskar ERP')); ?></h1>
                <p>Farmer medicine ERP and eCommerce system</p>
                <p>GST / Address details can be configured later.</p>
            </div>
            <div class="doc-title">
                <h2><?php echo e($title); ?></h2>
                <p><strong>No:</strong> <?php echo e($number); ?></p>
                <p><strong>Date:</strong> <?php echo e($date ? \Illuminate\Support\Carbon::parse($date)->format('d-m-Y') : '-'); ?></p>
                <?php if($validUntil): ?><p><strong>Valid Until:</strong> <?php echo e(\Illuminate\Support\Carbon::parse($validUntil)->format('d-m-Y')); ?></p><?php endif; ?>
                <span class="badge"><?php echo e(str($status)->replace('_', ' ')->title()); ?></span>
            </div>
        </div>

        <div class="section party-grid">
            <div class="box party">
                <h3>Bill To</h3>
                <p><strong><?php echo e($party); ?></strong></p>
                <p><?php echo e(str($order->order_type)->title()); ?> Order</p>
                <?php if($contact?->mobile): ?><p>Mobile: <?php echo e($contact->mobile); ?></p><?php endif; ?>
                <?php if($contact?->email): ?><p>Email: <?php echo e($contact->email); ?></p><?php endif; ?>
            </div>
            <div class="box meta">
                <h3>Order Details</h3>
                <p><strong>Sale Order:</strong> <?php echo e($order->order_no); ?></p>
                <p><strong>Channel:</strong> <?php echo e(str($order->order_type)->title()); ?></p>
                <p><strong>Salesman:</strong> <?php echo e($order->salesman?->name ?? '-'); ?></p>
            </div>
        </div>

        <div class="section">
            <table>
                <thead>
                    <tr>
                        <th style="width: 44px;">#</th>
                        <th>Product</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">GST</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($index + 1); ?></td>
                            <td><?php echo e($item['name']); ?></td>
                            <td class="text-end"><?php echo e(number_format((float) $item['quantity'], 3)); ?></td>
                            <td class="text-end">Rs. <?php echo e(number_format((float) $item['unit_price'], 2)); ?></td>
                            <td class="text-end"><?php echo e(number_format((float) $item['gst_percent'], 2)); ?>%<br>Rs. <?php echo e(number_format((float) $item['gst_amount'], 2)); ?></td>
                            <td class="text-end">Rs. <?php echo e(number_format((float) $item['line_total'], 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <table class="totals">
            <tr><td>Subtotal</td><td class="text-end">Rs. <?php echo e(number_format((float) $totals['subtotal'], 2)); ?></td></tr>
            <tr><td>GST Total</td><td class="text-end">Rs. <?php echo e(number_format((float) $totals['gst_total'], 2)); ?></td></tr>
            <tr><td>Discount</td><td class="text-end">Rs. <?php echo e(number_format((float) $totals['discount_total'], 2)); ?></td></tr>
            <tr class="grand"><td>Grand Total</td><td class="text-end">Rs. <?php echo e(number_format((float) $totals['grand_total'], 2)); ?></td></tr>
        </table>

        <div class="footer">
            <p>This is a computer-generated document.</p>
            <div class="signature">Authorised Signature</div>
        </div>
    </main>
</body>
</html><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\admin\sales-documents\print.blade.php ENDPATH**/ ?>