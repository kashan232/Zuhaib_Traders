@include('admin_panel.include.header_include')

<style>
    .search-container {
        position: relative;
        width: 100%;
        /* Adjust width as needed */
    }

    #productSearch {
        width: 100%;
        padding: 8px;
    }

    #searchResults {
        position: absolute;
        width: 100%;
        max-height: 200px;
        overflow-y: auto;
        background-color: #fff;
        border: 1px solid #ddd;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .search-result-item {
        padding: 10px;
        cursor: pointer;
    }

    .search-result-item:hover {
        background-color: #f0f0f0;
    }
</style>

<body>
    <!-- page-wrapper start -->
    <div class="page-wrapper default-version">

        <!-- sidebar start -->
        @include('admin_panel.include.sidebar_include')
        <!-- sidebar end -->

        <!-- navbar-wrapper start -->
        @include('admin_panel.include.navbar_include')
        <!-- navbar-wrapper end -->
        <div class="body-wrapper">
            <div class="bodywrapper__inner">

                <div class="d-flex mb-30 flex-wrap gap-3 justify-content-between align-items-center">
                    <h6 class="page-title">Add Sale</h6>
                    <div class="d-flex flex-wrap justify-content-end gap-2 align-items-center breadcrumb-plugins">
                        <a href="https://script.viserlab.com/torylab/admin/purchase/all"
                            class="btn btn-sm btn-outline--primary">
                            <i class="la la-undo"></i> Back</a>
                    </div>
                </div>

                <div class="row gy-3">
                    <div class="col-lg-12 col-md-12 mb-30">
                        <div class="card">
                            <div class="card-body">
                                @if (session()->has('error'))
                                <div class="alert alert-danger">
                                    <strong>Error!</strong> {{ session('error') }}.
                                </div>
                                @endif
                                <form action="{{ route('store-Sale') }}" method="POST">
                                    @csrf
                                    <div class="row mb-3">
                                        <div class="col-xl-4 col-sm-6">
                                            <label class="form-label">Customers</label>
                                            <select name="customer_info" id="customer-select" class="select2-basic form-control" required>
                                                <option selected disabled>Select One</option>
                                                @foreach($Customers as $Customer)
                                                <option value="{{ $Customer->id . '|' . $Customer->customer_name }}">{{ $Customer->customer_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-xl-4 col-sm-6">
                                            <label>Date</label>
                                            <input name="sale_date" type="date" class="form-control bg--white" required>
                                        </div>
                                        <div class="col-xl-4 col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label">Warehouse</label>
                                                <select name="warehouse_id" class="form-control" required>
                                                    <option selected disabled>Select One</option>
                                                    @foreach($Warehouses as $Warehouse)
                                                    <option value="{{ $Warehouse->name }}">{{ $Warehouse->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table border">
                                            <thead class="border bg--dark">
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Product</th>
                                                    <th>Quantity</th>
                                                    <th>T.P</th>
                                                    <th>Gross Amnt</th>
                                                    <th>Discount 14%</th>
                                                    <th>After 14%</th>
                                                    <th>Discount 7%</th>
                                                    <th>After 7%</th>
                                                    <th>Final Total</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="purchaseItems"></tbody>
                                        </table>
                                        <button type="button" class="btn btn-primary mt-2" id="addRow">Add More</button>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-3">
                                            <label>Net Amount</label>
                                            <input type="text" name="net_amount" class="form-control" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Discount 13%</label>
                                            <input type="number" name="discount_13" class="form-control" id="discount_13" placeholder="Enter Discount 13%">
                                        </div>
                                        <div class="col-md-3">
                                            <label>After 13%</label>
                                            <input type="text" name="after_discount_13" class="form-control" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Discount 2%</label>
                                            <input type="number" name="discount_2" class="form-control" id="discount_2" placeholder="Enter Discount 2%">
                                        </div>
                                        <div class="col-md-3">
                                            <label>After 2%</label>
                                            <input type="text" name="after_discount_2" class="form-control" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Scheme Minus</label>
                                            <input type="number" name="scheme_minus" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label>Total Amount</label>
                                            <input type="text" name="total_amount" class="form-control" readonly>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Previous Balance</label>
                                                <input type="text" class="form-control" id="previous_balance" name="previous_balance" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Closing Balance</label>
                                                <div class="input-group">
                                                    <input type="text" id="closing_balance" name="closing_balance" class="form-control" readonly>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <button type="submit" class="btn btn--primary w-100 h-45 mt-3">Submit</button>

                                </form>


                            </div>
                        </div>
                    </div>
                </div>


            </div><!-- bodywrapper__inner end -->
        </div><!-- body-wrapper end -->

    </div>
    @include('admin_panel.include.footer_include')

    <script>
        $(document).ready(function() {
            $('#customer-select').change(function() {
                const customerData = $(this).val().split('|');
                const customerId = customerData[0];
                if (customerId) {
                    $.ajax({
                        url: "{{ route('get-customer-amount', ':id') }}".replace(':id', customerId),
                        type: 'GET',
                        success: function(response) {
                            $('#previous_balance').val(response.previous_balance || 0);
                            updateClosingBalance(); // Pehlay balance ko calculate karne k liye
                        },
                        error: function(xhr) {
                            console.error("Customer ka amount fetch krne me masla aya: ", xhr);
                        }
                    });
                }
            });

            // Naya row add karne ka function
            $('#addRow').click(function() {
                const newRow = createNewRow();
                $('#purchaseItems').append(newRow);
            });

            function createNewRow() {
                return `
            <tr>
                <td>
                    <select name="item_category[]" style="width:150px;" class="form-control item-category" required>
                        <option value="" disabled selected>Category Chunein</option>
                        @foreach($Category as $Categories)
                            <option value="{{ $Categories->category }}">{{ $Categories->category }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="item_name[]" style="width:150px;" class="form-control item-name" required>
                        <option value="" disabled selected>Product Chunein</option>
                    </select>
                </td>
                <td><input type="number" style="width:150px;" name="quantity[]" class="form-control quantity" required></td>
                <td><input type="number" style="width:150px;" name="price[]" class="form-control price" value="0" required></td>
                <td><input type="number" style="width:150px;" name="gross_amnt[]" class="form-control gross-amnt" readonly></td>
                <td><input type="number" style="width:150px;" name="discount_14[]" class="form-control discount_14"></td>
                <td><input type="number" style="width:150px;" name="after_14[]" class="form-control after_14" readonly></td>
                <td><input type="number" style="width:150px;" name="discount_7[]" class="form-control discount_7"></td>
                <td><input type="number" style="width:150px;" name="after_7[]" class="form-control after_7" readonly></td>
                <td><input type="number" style="width:150px;" name="final_total[]" class="form-control final-total" readonly></td>
                <td><button type="button" class="btn btn-danger remove-row">Delete</button></td>
            </tr>`;
            }

            $('#purchaseItems').on('input', '.quantity, .price, .discount_14, .discount_7', function() {
                const row = $(this).closest('tr');
                const quantity = parseFloat(row.find('.quantity').val()) || 0;
                const price = parseFloat(row.find('.price').val()) || 0;
                const grossAmnt = quantity * price;
                row.find('.gross-amnt').val(Math.round(grossAmnt));

                const discount14 = parseFloat(row.find('.discount_14').val()) || 0;
                const after14 = grossAmnt - (grossAmnt * discount14 / 100);
                row.find('.after_14').val(Math.round(after14));

                const discount7 = parseFloat(row.find('.discount_7').val()) || 0;
                const after7 = after14 - (after14 * discount7 / 100);
                row.find('.after_7').val(Math.round(after7));

                row.find('.final-total').val(Math.round(after7));

                calculateTotalPrice();
            });

            function calculateTotalPrice() {
                let netAmount = 0;
                $('#purchaseItems tr').each(function() {
                    const finalTotal = parseFloat($(this).find('.final-total').val()) || 0;
                    netAmount += finalTotal;
                });

                $('input[name="net_amount"]').val(Math.round(netAmount));

                calculateDiscounts(netAmount);
            }

            function calculateDiscounts(netAmount) {
                let discount13 = 0;
                let afterDiscount13 = netAmount;

                if ($('#discount_13').val()) {
                    discount13 = Math.round(netAmount * 0.13);
                    afterDiscount13 = netAmount - discount13;
                }

                let discount2 = 0;
                let afterDiscount2 = afterDiscount13;

                if ($('#discount_2').val()) {
                    discount2 = Math.round(afterDiscount13 * 0.02);
                    afterDiscount2 = afterDiscount13 - discount2;
                }

                const schemeMinus = parseFloat($('input[name="scheme_minus"]').val()) || 0;
                const totalAmount = afterDiscount2 - schemeMinus;

                $('input[name="discount_13"]').val(discount13);
                $('input[name="after_discount_13"]').val(afterDiscount13);
                $('input[name="discount_2"]').val(discount2);
                $('input[name="after_discount_2"]').val(afterDiscount2);
                $('input[name="total_amount"]').val(Math.round(totalAmount));

                updateClosingBalance();
            }

            function updateClosingBalance() {
                const previousBalance = parseFloat($('#previous_balance').val()) || 0;
                const totalAmount = parseFloat($('input[name="total_amount"]').val()) || 0;
                const closingBalance = previousBalance + totalAmount;

                $('#closing_balance').val(Math.round(closingBalance));
            }

            $('#discount_13, #discount_2, input[name="scheme_minus"]').on('input', function() {
                calculateTotalPrice();
            });

            $('#purchaseItems').on('change', '.item-category', function() {
                const categoryName = $(this).val();
                const row = $(this).closest('tr');
                const itemSelect = row.find('.item-name');

                if (categoryName) {
                    fetch(`{{ route('get-items-by-category', ':categoryId') }}`.replace(':categoryId', categoryName))
                        .then(response => response.json())
                        .then(items => {
                            itemSelect.html('<option value="" disabled selected>Product Chunein</option>');
                            items.forEach(item => {
                                itemSelect.append(`<option value="${item.product_name}" data-price="${item.retail_price}">${item.product_name}</option>`);
                            });
                        })
                        .catch(error => console.error('Items fetch krne me masla aya:', error));
                }
            });

            $('#purchaseItems').on('change', '.item-name', function() {
                const productName = $(this).val();
                const row = $(this).closest('tr');
                const priceInput = row.find('.price');

                if (productName) {
                    const selectedOption = $(this).find('option:selected');
                    const price = selectedOption.data('price') || 0;
                    priceInput.val(price);
                    priceInput.trigger('input');
                }
            });

            $('#purchaseItems').on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                calculateTotalPrice();
            });

            $('#discount_13, #discount_2, input[name="scheme_minus"]').on('input', function() {
                const netAmount = parseFloat($('input[name="net_amount"]').val()) || 0;
                calculateDiscounts(netAmount);
            });

        });
    </script>


    <!-- <script>
        $(document).ready(function() {
            // Customer selection change
            $('#customer-select').change(function() {
                const customerData = $(this).val().split('|');
                const customerId = customerData[0];
                // alert(customerId);
                if (customerId) {
                    $.ajax({
                        url: "{{ route('get-customer-amount', ':id') }}".replace(':id', customerId),
                        type: 'GET',
                        success: function(response) {
                            $('#previous_balance').val(response.previous_balance || 0);
                            updateClosingBalance(); // Calculate closing balance initially
                        },
                        error: function(xhr) {
                            console.error("Error fetching customer amount: ", xhr);
                        }
                    });
                }
            });

            // Update total price and payable amount on input change
            $('input[name="total_price"]').on('input', calculateTotalPrice);
            $('#discount').on('input', calculatePayableAmount);
            $('#cashReceived').on('input', updateClosingBalance); // Trigger closing balance update on cash received input

            // Function to calculate total price
            function calculateTotalPrice() {
                let total = 0;
                $('#purchaseItems tr').each(function() {
                    const quantity = parseFloat($(this).find('.quantity').val()) || 0;
                    const price = parseFloat($(this).find('.price').val()) || 0;
                    total += quantity * price;
                });

                $('.total_price').val(total.toFixed(2));
                calculatePayableAmount(); // Update payable amount
            }

            // Function to calculate payable amount
            function calculatePayableAmount() {
                function calculatePayableAmount() {
                    let totalPrice = parseFloat($('.total_price').val()) || 0;
                    let discount1 = parseFloat($('#discount_1').val()) || 0;
                    let discount2 = parseFloat($('#discount_2').val()) || 0;
                    let discount3 = parseFloat($('#discount_3').val()) || 0;
                    let toValue = parseFloat($('#to_value').val()) || 0;
                    let toType = $('input[name="to_type"]:checked').val();

                    // Apply discounts sequentially
                    let discountedPrice = totalPrice;
                    discountedPrice -= (discountedPrice * discount1) / 100;
                    discountedPrice -= (discountedPrice * discount2) / 100;
                    discountedPrice -= (discountedPrice * discount3) / 100;

                    // Apply TO Calculation
                    if (toType === 'percentage') {
                        discountedPrice -= (discountedPrice * toValue) / 100;
                    } else {
                        discountedPrice -= toValue;
                    }

                    // Set the value with 2 decimal places
                    $('.payable_amount').val(discountedPrice.toFixed(2));
                    updateClosingBalance();
                }


                // Trigger calculation on input change
                $('#discount_1, #discount_2, #discount_3, #to_value').on('input', calculatePayableAmount);
                $('input[name="to_type"]').on('change', calculatePayableAmount);
            }

            // Function to update closing balance
            function updateClosingBalance() {
                const previousBalance = parseFloat($('#previous_balance').val()) || 0;
                const payableAmount = parseFloat($('.payable_amount').val()) || 0;
                const cashReceived = parseFloat($('#cashReceived').val()) || 0;

                const closingBalance = Math.max(0, previousBalance + payableAmount - cashReceived);

                $('#closing_balance').val(closingBalance.toFixed(2));
            }

            // Add a new row
            $('#addRow').click(function() {
                const newRow = createNewRow();
                $('#purchaseItems').append(newRow);
                calculateTotalPrice();
            });

            // Function to create a new row
            function createNewRow(category = '', productName = '', price = '') {
                return `
            <tr>
                <td>
                    <select name="item_category[]" class="form-control item-category" required>
                        <option value="" disabled ${category ? '' : 'selected'}>Select Category</option>
                        @foreach($Category as $Categories)
                            <option value="{{ $Categories->category }}" ${category === '{{ $Categories->category }}' ? 'selected' : ''}>{{ $Categories->category }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="item_name[]" class="form-control item-name" required>
                        <option value="" disabled ${productName ? '' : 'selected'}>Select Item</option>
                        <option value="${productName}" selected>${productName}</option>
                    </select>
                </td>
                <td><input type="number" name="quantity[]" class="form-control quantity" required></td>
                <td><input type="number" name="price[]" class="form-control price" value="${price}" required></td>
                <td><input type="number" name="total[]" class="form-control total" readonly></td>
                <td>
                    <button type="button" class="btn btn-danger remove-row">Delete</button>
                </td>
            </tr>`;
            }

            // Remove a row
            $('#purchaseItems').on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                calculateTotalPrice();
            });

            // Update row total on quantity or price change
            $('#purchaseItems').on('input', '.quantity, .price', function() {
                const row = $(this).closest('tr');
                const quantity = parseFloat(row.find('.quantity').val()) || 0;
                const price = parseFloat(row.find('.price').val()) || 0;
                row.find('.total').val((quantity * price).toFixed(2));
                calculateTotalPrice();
            });

            // Fetch items based on category
            $('#purchaseItems').on('change', '.item-category', function() {
                const categoryName = $(this).val();
                const row = $(this).closest('tr');
                const itemSelect = row.find('.item-name');

                if (categoryName) {
                    fetch(`{{ route('get-items-by-category', ':categoryId') }}`.replace(':categoryId', categoryName))
                        .then(response => response.json())
                        .then(items => {
                            itemSelect.html('<option value="" disabled selected>Select Item</option>');
                            items.forEach(item => {
                                itemSelect.append(`<option value="${item.product_name}">${item.product_name}</option>`);
                            });
                        })
                        .catch(error => console.error('Error fetching items:', error));
                }
            });

            // Fetch product details based on selected product
            $('#purchaseItems').on('change', '.item-name', function() {
                const productName = $(this).val();
                const row = $(this).closest('tr');
                const priceInput = row.find('.price');

                if (productName) {
                    fetch(`{{ route('get-product-details', ':productName') }}`.replace(':productName', productName))
                        .then(response => response.json())
                        .then(product => {
                            priceInput.val(product.retail_price);
                        })
                        .catch(error => console.error('Error fetching product details:', error));
                }
            });

            // Search product functionality
            $('#productSearch').on('keyup', function() {
                const query = $(this).val();
                if (query.length > 0) {
                    $.ajax({
                        url: "{{ route('search-products') }}",
                        type: 'GET',
                        data: {
                            q: query
                        },
                        success: displaySearchResults,
                        error: function(error) {
                            console.error('Error in product search:', error);
                        }
                    });
                } else {
                    $('#searchResults').html('');
                }
            });

            // Display search results
            function displaySearchResults(products) {
                const searchResults = $('#searchResults');
                searchResults.html('');
                products.forEach(product => {
                    const listItem = `<li class="list-group-item search-result-item" data-category="${product.category}" data-product-name="${product.product_name}" data-price="${product.retail_price}">
                    ${product.category} - ${product.product_name} - ${product.retail_price}
                </li>`;
                    searchResults.append(listItem);
                });
            }

            // Add searched product as a new row
            $('#searchResults').on('click', '.search-result-item', function() {
                const category = $(this).data('category');
                const productName = $(this).data('product-name');
                const price = $(this).data('price');

                const newRow = createNewRow(category, productName, price);
                $('#purchaseItems').append(newRow);
                $('#searchResults').html('');
                calculateTotalPrice();
            });
        });
    </script> -->

</body>