// securepay-wc-admin.js
jQuery(function ($) {
    'use strict';

    $(document).ready(function () {
        // Auto-format Credit Card Number with spaces (e.g., 4111 1111 1111 1111)
        $('#ccardNumber').on('input', function () {
            let value = $(this).val().replace(/\D/g, ''); // Remove all non-digit characters
            value = value.match(/.{1,4}/g)?.join(' ') || value; // Insert a space every 4 digits
            $(this).val(value);

            // Simple validation for 16-digit card starting with 4 (Visa)
            let regex = /^4\d{3} \d{4} \d{4} \d{4}$/; // Match Visa card format
            if (value.length === 19 && regex.test(value)) {
                $(this).css('border', '2px solid green !important'); // Valid format
            } else {
                $(this).css('border', '2px solid red !important'); // Invalid format
            }
        });

        // Expiry Date Validation
        $('#exyear, #exmonth').on('change', function () {
            let month = parseInt($('#exmonth').val(), 10); // Get selected month as an integer
            let year = parseInt($('#exyear').val(), 10); // Get selected year as an integer
            let today = new Date(); // Current date
            let selectedDate = new Date(year, month - 1); // JavaScript months are zero-based

            if (selectedDate > today) {
                $('#exmonth, #exyear').css('border', '2px solid green !important'); // Expiry date is valid
            } else {
                $('#exmonth, #exyear').css('border', '2px solid red !important'); // Expiry date is in the past
            }
        });

        // Ensure if both are selected and valid, borders are green
        $('#exyear, #exmonth').trigger('change');

        // CVV Validation: Ensure CVV is a 3-digit number
        $('#ccvv').on('input', function () {
            let value = $(this).val();
            if (value.length === 3 && /^\d{3}$/.test(value)) {
                $(this).css('border', '2px solid green !important'); // Valid CVV
            } else {
                $(this).css('border', '2px solid red !important'); // Invalid CVV
            }
        });
    });
});
