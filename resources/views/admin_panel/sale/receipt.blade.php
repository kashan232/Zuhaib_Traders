<!DOCTYPE html>
<html>

<head>
    <title>Sale Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .receipt-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 2px solid #000;
            background-color: #fff;
            page-break-after: always;
        }

        h2, h4 {
            margin: 5px 0;
        }

        .heading {
            text-align: center;
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .invoice-details div {
            width: 48%;
        }

        .invoice-details .left {
            text-align: left;
        }

        .invoice-details .right {
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table, th, td {
            border: 1px solid #ccc;
            text-align: center;
            padding: 10px;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .highlight {
            background-color: red;
            color: white;
        }

        .total-section {
            margin-top: 20px;
            text-align: right;
            font-size: 1.1em;
        }

        .total-section p {
            margin: 5px 0;
        }

        .no-print {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 1em;
        }

        .no-print:hover {
            background-color: #0056b3;
        }

        @media print {
            .no-print {
                display: none;
            }

            @page {
                size: A4;
                margin: 20mm;
            }

            .receipt-container {
                width: 100%;
                padding: 20px;
                page-break-after: always;
            }
        }
    </style>
</head>

<body>

    <div class="receipt-container">
        <div class="heading">
            HR TRADERS HYDERABAD
        </div>
        <div class="invoice-details">
            <div class="left">
                <h4>Invoice No: {{ $sale->invoice_no }}</h4>
                <h4>Customer: {{ $sale->customer }}</h4>
            </div>
            <div class="right">
                <h4>Date: {{ $sale->sale_date }}</h4>
                <h4>Warehouse: {{ $sale->warehouse_id }}</h4>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Gross Amount</th>
                    <th>Disc. 14%</th>
                    <th>After Disc. 14%</th>
                    <th>Disc. 7%</th>
                    <th>After Disc. 7%</th>
                    <th class="highlight">Final Amount</th>
                </tr>
            </thead>
            <tbody>
                @php
                $items = json_decode($sale->item_name, true) ?? [];
                $quantities = json_decode($sale->quantity, true) ?? [];
                $prices = json_decode($sale->price, true) ?? [];
                $grossAmounts = json_decode($sale->gross_amount, true) ?? [];
                $discount14 = json_decode($sale->discount_14, true) ?? [];
                $after14 = json_decode($sale->after_14, true) ?? [];
                $discount7 = json_decode($sale->discount_7, true) ?? [];
                $after7 = json_decode($sale->after_7, true) ?? [];
                $finalTotals = json_decode($sale->final_total, true) ?? [];
                @endphp

                @foreach ($items as $key => $product)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $product }}</td>
                    <td>{{ $quantities[$key] ?? 0 }}</td>
                    <td>{{ $prices[$key] ?? 0 }}</td>
                    <td>{{ $grossAmounts[$key] ?? 0 }}</td>
                    <td>{{ $discount14[$key] ?? 0 }}</td>
                    <td>{{ $after14[$key] ?? 0 }}</td>
                    <td>{{ $discount7[$key] ?? 0 }}</td>
                    <td>{{ $after7[$key] ?? 0 }}</td>
                    <td>{{ $finalTotals[$key] ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-section">
            <p><strong>Net Amount:</strong> {{ $netTotal }}</p>
            <p><strong>Discount 13%:</strong> {{ $sale->discount_13 }} | After Discount: {{ $sale->after_discount_13 }}</p>
            <p><strong>Discount 2%:</strong> {{ $sale->discount_2 }} | After Discount: {{ $sale->after_discount_2 }}</p>
            <p><strong>Bonus Scheme:</strong> {{ $sale->scheme_minus }}</p>
            <p><strong>Total Amount:</strong> {{ $sale->total_amount }}</p>
            <p><strong>Previous Balance:</strong> {{ $previousBalance }}</p>
            <p><strong>Closing Balance:</strong> {{ $closingBalance }}</p>
        </div>

        <div style="width: 100%;">
            <p style="text-align: center; font-size: 0.9em; color: #888;">Designed & Developed by ProWave Software Solution</p>
        </div>
        <button class="no-print" onclick="window.print()">Print Receipt</button>
    </div>

</body>

</html>
