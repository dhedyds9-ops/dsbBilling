<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Voucher</title>
    @vite(["resources/css/app.css"])
    <style>
        body, body:not(.dark), .dark body {
            background: #fff !important;
            font-family: sans-serif;
            margin: 0;
            padding: 0;
        }
                @media print {
            .print-container {
                margin-top: 0 !important;
                padding: 0 !important;
            }
            
        }
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            body, body:not(.dark), .dark body {
                background: transparent !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                margin: 5mm;
            }
        }
        
        .top-bar {
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            padding: 5px 15px;
            display: flex;
            gap: 0px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        .top-bar button {
            border: 1px solid #777;
            background: #f0f0f0;
            padding: 2px 10px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 2px;
        }
        .top-bar button:hover {
            background: #e0e0e0;
        }

        .right-panel {
            position: fixed;
            right: 20px;
            bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 0px;
            z-index: 1000;
        }
        .right-panel button {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 8px 15px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            color: #333;
            text-align: center;
            min-width: 150px;
        }
        .right-panel button:hover {
            background: #f8f9fa;
        }

        .print-container {
            margin-top: 50px;
            padding: 10px;
            width: 100%;
        }

                .print-area .voucher-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0px;
            justify-content: flex-start;
            align-content: flex-start;
        }
        
                
        .print-area .voucher-card {
            page-break-inside: avoid;
            break-inside: avoid;
            overflow: hidden;
            display: inline-block;
        }
        
        .print-area .voucher-card > * {
            max-width: 100%;
            height: auto;
            transform-origin: top left;
        }
    </style>
</head>
<body>

    <div class="top-bar no-print">
        <button onclick="window.print()">CLICK TO PRINT</button>
        <button onclick="window.close()">CLOSE</button>
    </div>

    <div class="right-panel no-print">
        <button onclick="window.print()" style="color: #0d6efd; font-weight: bold;">Print</button>
        <button onclick="window.close()">Close</button>
    </div>

    @if(!empty($errors))
        <div class="p-8 max-w-4xl mx-auto no-print" style="margin-top:50px;">
            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <p class="font-bold">Terjadi Kesalahan pada Template:</p>
                <ul class="list-disc ml-5 mt-2 text-sm">
                    @foreach($errors as $error)
                        <li>{!! $error !!}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="print-container">
        <div class="print-area">
            {!! $html !!}
        </div>
    </div>

</body>
</html>






