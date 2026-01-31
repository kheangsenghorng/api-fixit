<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel PayWay Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">PayWay Checkout</h2>
        
        <form action="{{ route('payment.payway.card') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Amount (USD)</label>
                <input type="number" name="amount" value="1.00" step="0.01" required 
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <button type="submit" 
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-200">
                Proceed to Payment
            </button>
        </form>
        
        <p class="mt-4 text-xs text-gray-500 text-center">
            You will be redirected to ABA PayWay secure payment gateway.
        </p>
    </div>

</body>
</html>