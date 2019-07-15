var Lists = function () 
{
    var initTables = function () { 
        $('.data-list').each(function() {
            var id = $(this).attr('id');
            var table = $('#' + id);
            var page = $(this).attr('page');
            var order = $(this).attr('order');
            var method = $(this).attr('callbackMethod');
            Lists.initDataTable(table,order,page,method);
        });  
    }

    return {

        init: function () 
        {
            if (!jQuery().dataTable) 
            {
                return false;
            }

            initTables();
        },
        initDataTable : function(table,orderColumns,pages,method)
        {
            var order = (orderColumns != undefined && orderColumns != '') ? orderColumns : 'asc';
            var page = (pages != undefined && pages != '') ? pages : 10;
            var callbackMethod = (method != undefined && method != '') ? method : '';

            var oTable = table.dataTable({
                "language": 
                {
                    "aria": 
                    {
                        "sortAscending": ": activate to sort column ascending",
                        "sortDescending": ": activate to sort column descending"
                    },
                    "emptyTable": "No data available in table",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "infoEmpty": "No entries found",
                    "infoFiltered": "(filtered1 from _MAX_ total entries)",
                    "lengthMenu": "_MENU_ entries",
                    "search": "Search:",
                    "zeroRecords": "No matching records found"
                },
                buttons: [
                    { extend: 'print', className: 'btn dark btn-outline' },
                    { extend: 'copy', className: 'btn red btn-outline' },
                    { extend: 'pdf', className: 'btn green btn-outline' },
                    { extend: 'excel', className: 'btn yellow btn-outline ' },
                    { extend: 'csv', className: 'btn purple btn-outline ' },
                    { extend: 'colvis', className: 'btn dark btn-outline', text: 'Columns'}
                ],
                responsive: true,
                "order": [
                    [0, order]
                ],

                "lengthMenu": [
                    [5, 10, 15, 20, -1],
                    [5, 10, 15, 20, "All"]
                ],
                "pageLength": page,
                "fnDrawCallback": function(oSettings) 
                {
                    if(callbackMethod != '')
                    {
                        App.executeFunctionByName(callbackMethod,window);
                    }
                }
            });

            // handle datatable custom tools
            $('#data-list-tools > li > a.tool-action').on('click', function() {
                var action = $(this).attr('data-action');
                oTable.DataTable().button(action).trigger();
            });
        },
        updateTable : function(id,data)
        {
            var datatable = $('#' + id).dataTable().api();
            datatable.clear();
            datatable.rows.add(data); 
            datatable.draw();
        }
    };
}();

// initialize and activate the script
$(function(){ Lists.init(); });
