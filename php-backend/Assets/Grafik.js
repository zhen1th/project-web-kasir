const swiper = new Swiper('.swiper', {
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
    loop: false
});

chunksBulan.forEach((bulanGroup, index) => {
    new Chart(document.getElementById(`chart${index}`), {
        type: 'bar',
        data: {
            labels: bulanGroup,
            datasets: [{
                label: 'Pemasukan Bulanan',
                data: chunksNominal[index],
                backgroundColor: '#2C2C2C',
                borderColor: '#2C2C2C',
                borderWidth: 1,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: 14
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    },
                    title: {
                        display: true,
                        text: 'Nominal'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Bulan'
                    }
                }
            }
        }
    });
});
