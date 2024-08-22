document.addEventListener("DOMContentLoaded", function() {
    var elems = document.querySelectorAll("select");
    if (elems !== undefined && elems.length > 0) {
        var selectInstances = M.FormSelect.init(elems);
    }

    var elems2 = document.querySelectorAll('.tooltipped');
    if (elems2 !== undefined && elems2.length > 0) {
        var tooltipInstances = M.Tooltip.init(elems2);
    }

    var elems3 = document.querySelectorAll(".tabs");
    if (elems3 !== undefined && elems3.length > 0) {
        var tabsInstances = M.Tabs.init(elems3, {});
    }
});
