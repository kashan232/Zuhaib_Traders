<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Memo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            background-color: #f4f4f4;
        }

        .receipt-container {
            width: 100%;
            max-width: 450px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .receipt-header {
            text-align: center;
        }

        .receipt-header img {
            width: 80px;
            margin-bottom: 10px;
        }

        h2 {
            font-size: 20px;
            font-weight: bold;
            margin: 5px 0;
            color: #333;
        }

        .receipt-header p {
            margin: 2px 0;
            font-size: 12px;
            color: #555;
        }

        .details {
            margin: 15px 0;
            line-height: 1.6;
            font-size: 13px;
        }

        .details p {
            margin: 4px 0;
        }

        .details strong {
            color: #333;
        }

        table {
            width: 100%;
            margin-top: 10px;
            font-size: 12px;
            border-collapse: collapse;
        }

        th, td {
            padding: 6px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            font-weight: bold;
            background-color: #f9f9f9;
            color: #333;
        }

        .totals td {
            font-weight: bold;
            text-align: right;
        }

        .receipt-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #555;
        }

        .note {
            font-size: 13px;
            font-weight: bold;
            color: #d9534f;
            margin-bottom: 10px;
        }

        @media print {
            body {
                margin: 0;
            }

            .receipt-container {
                border: none;
                box-shadow: none;
                margin: 0;
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="receipt-container">
        <!-- Receipt Header -->
        <div class="receipt-header">
            <img src="../assets/admin/images/BB_logo.png" alt="Company Logo">
            <h2>Beauty Base Cosmetics</h2>
            <p><strong>Cash Memo</strong></p>
            <p><strong>Address:</strong> Resham Bazar</p>
            <p><strong>Phone:</strong> 0313-300452-0</p>
        </div>

        <!-- Sale Details -->
        <div class="details">
            <p><strong>Memo No:</strong> {{ $sale->invoice_no }}</p>
            <p><strong>Customer:</strong> {{ $sale->customer }}</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->created_at)->format('F d, Y h:i A') }}</p>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach(json_decode($sale->item_name) as $key => $item)
                <tr>
                    <td>{{ $item }}</td>
                    <td>{{ json_decode($sale->quantity)[$key] }}</td>
                    <td>{{ number_format(json_decode($sale->price)[$key], 0) }}</td>
                    <td>{{ number_format(json_decode($sale->total)[$key], 0) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals">
                    <td colspan="3">Total Amount</td>
                    <td>{{ $sale->total_price }}</td>
                </tr>
                <tr class="totals">
                    <td colspan="3">Discount</td>
                    <td>{{ $sale->discount }}</td>
                </tr>
                <tr class="totals">
                    <td colspan="3">Net Total</td>
                    <td><strong>{{ $sale->Payable_amount }}</strong></td>
                </tr>
                <tr class="totals">
                    <td colspan="3">Previous Balance</td>
                    <td>{{ $previous_balance }}</td>
                </tr>
                <tr class="totals">
                    <td colspan="3">Closing Balance</td>
                    <td>{{ $closing_balance }}</td>
                </tr>
                <tr class="totals">
                    <td colspan="3">Cash Received</td>
                    <td>{{ $sale->cash_received }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Footer Message -->
        <div class="receipt-footer">
            <p class="note">Goods Once Sold Are Not Returnable</p>
            <p>Thank you for your purchase!</p>
            <p>Designed & Developed by ProWave Software Solution</p>
            <p><strong>0317-3836223 | 0317-3859647</strong></p>
        </div>
    </div>

    <script>
        window.onload = function () {
            window.print();
            setTimeout(function () {
                window.location.href = "{{ route('all-sales') }}";
            }, 1000);
        };
    </script>
</body>

</html>
