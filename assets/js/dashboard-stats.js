function loadDashboardStats() {

    fetch("api/dashboard_stats.php")
        .then(res => res.json())
        .then(data => {

            if (data.error) return;

            document.getElementById("todaySales").innerText =
                "₱ " + Number(data.todaySales).toLocaleString("en-PH", { minimumFractionDigits: 2 });

            document.getElementById("todayTrans").innerText =
                data.todayTrans;

            document.getElementById("lowStockCount").innerText =
                data.lowStockCount;

            document.getElementById("supplierCount").innerText =
                data.supplierCount;

        });

}

loadDashboardStats();
setInterval(loadDashboardStats, 5000);