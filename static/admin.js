document.addEventListener("DOMContentLoaded", function() {
                var elems = document.querySelectorAll("select");
                if(elems!=undefined && count(elems))
                var instances = M.FormSelect.init(elems);

                var elems2 = document.querySelectorAll('.tooltipped');
                if(elems2!=undefined && count(elems2))
                var instances2 = M.Tooltip.init(elems2);


                var elems3 = document.querySelectorAll(".tabs");
                if(elems3!=undefined && count(elems3))
                var instances = M.Tabs.init(elems3, {});
            });