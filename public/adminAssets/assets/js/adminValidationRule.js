$(document).ready(function() {

      // Function to get the next row index
  function getNextRowIndex() {
    var rows = $(".permissionRow"); // Get all rows with the class permissionRow

        var lastRow = rows.last(); // Get the last row

    //var lastRow = $(".permissionRow:last-child");
    if (lastRow.length>1) {
        var lastRowId = lastRow.attr("id");
        if (lastRowId) {
            return parseInt(lastRowId.split('addr')[1]) + 1;
        }
    }
    return lastRow.length >0 ? parseInt(lastRow.attr("id").split('addr')[1]) :0 ; // Default starting index if no rows exist
}

var rowIndex = getNextRowIndex(); // Initialize row index
   // var rowIndex = parseInt($(".permissionRow:last-child").attr("id").split('addr')[1]); // Start index from 2 to match existing rows

   if(rowIndex >1){
        rowIndex=rowIndex+1;
    }

    // Add a new row
    $("#add_row").click(function() {
        var lastRow = $("#addr" + (rowIndex - 1));
        var newRow = lastRow.clone().attr("id", "addr" + rowIndex);

        // Clear input values in the new row
        newRow.find("input").each(function() {
            $(this).val(""); // Clear value
            $(this).removeAttr('readonly'); // Make sure input is not readonly
        });

        // Update the row index
        newRow.find("td:first-child").html(rowIndex + 1);
        $("#tab_logic").append(newRow);
        rowIndex++;
    });

    // Delete the last row
    $("#delete_row").click(function() {
        var rows = $(".permissionRow"); // Get all rows with the class permissionRow

        if (rows.length > 1) { // Ensure at least one row remains
            var lastRow = rows.last(); // Get the last row
            lastRow.remove(); // Remove the last row
            rowIndex--; // Decrement the row index
        }
    });

    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const passwordEyeIcon = document.getElementById('password-eye-icon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordEyeIcon.classList.remove('feather-eye');
            passwordEyeIcon.classList.add('feather-eye-off'); // Change to your eye-off icon class
        } else {
            passwordInput.type = 'password';
            passwordEyeIcon.classList.remove('feather-eye-off');
            passwordEyeIcon.classList.add('feather-eye'); // Change to your eye icon class
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('customerForm');
        form.addEventListener('submit', function (event) {
            const phoneInput = document.querySelector('input[name="phone_no"]');
            const phoneValue = phoneInput.value;

            if (!/^\d{10}$/.test(phoneValue)) {
                event.preventDefault(); // Prevent form submission
                alert('Please enter a valid 10-digit phone number.');
                phoneInput.focus();
            }
        });
    });
});
