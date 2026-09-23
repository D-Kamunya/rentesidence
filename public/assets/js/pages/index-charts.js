$(function () {
    "use strict";
    // Centresidence design language: cs-blue gradient bars, rounded, subtle grid,
    // muted axes, k-formatted y-axis and a clean light tooltip.
    var options = {
        series: [{
            name: "Rent collected",
            data: INVOICEMONTLYAMOUNT
        }],
        chart: {
            foreColor: '#9ca3af',
            fontFamily: 'inherit',
            type: "bar",
            height: 270,
            toolbar: { show: false },
            zoom: { enabled: false },
            sparkline: { enabled: false }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: "45%",
                borderRadius: 6,
                borderRadiusApplication: 'end'
            }
        },
        legend: { show: false },
        dataLabels: { enabled: false },
        grid: {
            show: true,
            borderColor: '#eef2f7',
            strokeDashArray: 4,
            xaxis: { lines: { show: false } },
            yaxis: { lines: { show: true } },
            padding: { left: 6, right: 6 }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.2,
                gradientToColors: ['#3b82f6'],
                inverseColors: false,
                opacityFrom: 1,
                opacityTo: 0.82,
                stops: [0, 100]
            }
        },
        colors: ["#185FA5"],
        states: { hover: { filter: { type: 'darken', value: 0.92 } } },
        xaxis: {
            categories: MONTHS,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { fontSize: '11px', colors: '#9ca3af' } }
        },
        yaxis: {
            labels: {
                style: { fontSize: '11px', colors: '#9ca3af' },
                formatter: function (val) {
                    return Math.abs(val) >= 1000 ? (val / 1000).toFixed(val % 1000 === 0 ? 0 : 1) + 'k' : val;
                }
            }
        },
        tooltip: {
            theme: 'light',
            y: {
                formatter: function (val) {
                    return currencyPrice(val)
                }
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#chart1"), options);
    chart.render();
    // Chart 1 End
});
