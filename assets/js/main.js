document.addEventListener('DOMContentLoaded', function() {
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        const targetWidth = progressBar.getAttribute('data-width');
        
        // Set width awal ke 0
        progressBar.style.width = '0%';
        
        // Berikan delay kecil sebelum animasi
        setTimeout(() => {
            // Animasikan ke target width
            progressBar.style.width = targetWidth + '%';
            
            // Update teks persentase di progress bar
            const percentage = progressBar.querySelector('.progress-percentage');
            if (percentage) {
                percentage.textContent = Math.round(targetWidth) + '%';
                percentage.style.opacity = '1'; // Tampilkan teks persentase
                percentage.style.transform = 'translateY(0)'; // Reset posisi
            }
        }, 300);
    }

    // Notifikasi deadline
    function checkDeadlines() {
        const tasks = document.querySelectorAll('.task-card');
        tasks.forEach(task => {
            const deadline = task.getAttribute('data-deadline');
            const today = new Date();
            const dueDate = new Date(deadline);
            const diffDays = Math.ceil((dueDate - today) / (1000 * 60 * 60 * 24));

            if (diffDays <= 3 && diffDays > 0) {
                showNotification(`Task "${task.querySelector('h5').textContent}" akan berakhir dalam ${diffDays} hari!`);
            }
        });
    }

    function showNotification(message) {
        if (Notification.permission === "granted") {
            new Notification("ToDo List Reminder", {
                body: message
            });
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    new Notification("ToDo List Reminder", {
                        body: message
                    });
                }
            });
        }
    }

    checkDeadlines();

    // Drag and drop untuk mengatur prioritas
    const taskCards = document.querySelectorAll('.task-card');
    taskCards.forEach(card => {
        card.setAttribute('draggable', true);
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragover', handleDragOver);
        card.addEventListener('drop', handleDrop);
    });

    // Initialize scrollable indicators
    const descriptions = document.querySelectorAll('.task-description');
    descriptions.forEach(desc => {
        if (desc.scrollHeight > desc.clientHeight) {
            desc.classList.add('scrollable');
        }
    });

    // Inisialisasi semua modal
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        new bootstrap.Modal(modal);
    });

    // Debug untuk memastikan event click terdeteksi
    const editButtons = document.querySelectorAll('.action-btn.edit');
    editButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            console.log('Edit button clicked');
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        initProgressBar();
    });
});

function handleDragStart(e) {
    e.dataTransfer.setData('text/plain', e.target.id);
}

function handleDragOver(e) {
    e.preventDefault();
}

function handleDrop(e) {
    e.preventDefault();
    const draggedId = e.dataTransfer.getData('text/plain');
    const droppedId = e.target.closest('.task-card').id;
    // Implementasi AJAX untuk memperbarui urutan prioritas
}

// Fungsi untuk validasi tanggal
function validateDate(input) {
    const selectedDate = new Date(input.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Reset waktu ke 00:00:00

    if (selectedDate < today) {
        input.value = today.toISOString().split('T')[0];
        document.getElementById('dateError').textContent = 'Tidak dapat memilih tanggal yang sudah lewat!';
        setTimeout(() => {
            document.getElementById('dateError').textContent = '';
        }, 3000);
    } else {
        document.getElementById('dateError').textContent = '';
    }
}

// Tambahkan fungsi untuk membuka modal
function openEditModal(id) {
    const modal = new bootstrap.Modal(document.getElementById('editModal' + id));
    modal.show();
}

// Tambahkan event listener saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Set tanggal minimum untuk semua input tanggal
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const selectedDate = new Date(dateInput.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (selectedDate < today) {
        e.preventDefault();
        document.getElementById('dateError').textContent = 'Pilih tanggal yang valid!';
        return false;
    }
    return true;
});

function toggleDescription(btn) {
    const description = btn.closest('.task-description');
    const content = description.querySelector('.task-description-content');
    const isExpanded = description.classList.contains('expanded');

    if (!isExpanded && content.scrollHeight > 100) {
        description.classList.add('expanded');
        btn.innerHTML = '<i class="fas fa-chevron-up"></i> Tutup';
    } else {
        description.classList.remove('expanded');
        btn.innerHTML = '<i class="fas fa-chevron-down"></i> Lihat Lebih';
        description.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
}

// Check if content needs expand button
document.addEventListener('DOMContentLoaded', function() {
    const descriptions = document.querySelectorAll('.task-description');
    descriptions.forEach(desc => {
        const content = desc.querySelector('.task-description-content');
        const overlay = desc.querySelector('.description-overlay');
        if (content.scrollHeight <= 100) {
            overlay.style.display = 'none';
        }
    });
});

// Add overflow detection
document.addEventListener('DOMContentLoaded', function() {
    const descriptions = document.querySelectorAll('.task-description');
    descriptions.forEach(desc => {
        const content = desc.querySelector('.task-description-content');
        if (content.scrollHeight > desc.clientHeight) {
            desc.classList.add('has-overflow');
        }

        // Update fade effect on scroll
        desc.addEventListener('scroll', function() {
            if (desc.scrollTop + desc.clientHeight >= content.scrollHeight - 30) {
                desc.classList.remove('has-overflow');
            } else {
                desc.classList.add('has-overflow');
            }
        });
    });
});

// Fungsi untuk menangani filter
document.addEventListener('DOMContentLoaded', function() {
    // Handle form filter submission
    const filterForm = document.querySelector('form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });

        // Reset button handler
        const resetButton = filterForm.querySelector('button[type="button"]');
        if (resetButton) {
            resetButton.addEventListener('click', function() {
                filterForm.reset();
                clearUrlParameters();
                applyFilters();
            });
        }
    }
});

// Fungsi untuk menerapkan filter
function applyFilters() {
    const priority = document.querySelector('select[name="filter_priority"]').value;
    const category = document.querySelector('select[name="filter_category"]').value;
    const date = document.querySelector('input[name="filter_date"]').value;
    const search = document.querySelector('input[name="search"]').value;
    const status = document.querySelector('input[name="filter_status"]').value;

    // Buat URL dengan parameter filter
    let url = new URL(window.location.href);
    url.searchParams.set('filter_priority', priority);
    url.searchParams.set('filter_category', category);
    url.searchParams.set('filter_date', date);
    url.searchParams.set('search', search);
    if (status) url.searchParams.set('filter_status', status);

    // Redirect ke URL dengan filter
    window.location.href = url.toString();
}

// Fungsi untuk membersihkan parameter URL
function clearUrlParameters() {
    window.location.href = window.location.pathname;
}

// Fungsi untuk memvalidasi input tanggal
function validateFilterDate(input) {
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    // Reset waktu ke 00:00:00 untuk perbandingan yang akurat
    today.setHours(0, 0, 0, 0);
    selectedDate.setHours(0, 0, 0, 0);

    // Tampilkan pesan jika tanggal yang dipilih sudah lewat
    if (selectedDate < today) {
        alert('Tanggal yang dipilih tidak valid. Harap pilih tanggal hari ini atau yang akan datang.');
        input.value = '';
        return false;
    }
    return true;
}

// Update fungsi untuk menampilkan hasil filter
function updateTaskVisibility() {
    const tasks = document.querySelectorAll('.task-card');
    const priority = document.querySelector('select[name="filter_priority"]').value;
    const category = document.querySelector('select[name="filter_category"]').value;
    const date = document.querySelector('input[name="filter_date"]').value;
    const search = document.querySelector('input[name="search"]').value.toLowerCase();

    tasks.forEach(task => {
        let show = true;

        // Filter prioritas
        if (priority) {
            const taskPriority = task.querySelector('.task-priority').getAttribute('data-priority') || '';
            if (taskPriority !== priority) {
                show = false;
            }
        }

        // Filter kategori 
        if (category) {
            const taskCategory = task.querySelector('.task-category').textContent.trim();
            if (taskCategory !== category) {
                show = false;
            }
        }

        // Filter tanggal 
        if (date) {
            const taskDate = task.querySelector('.task-meta-item:first-child').getAttribute('data-date');
            if (taskDate !== date) {
                show = false;
            }
        }

        // Filter pencarian
        if (search) {
            const searchableContent = [
                task.querySelector('.task-title').textContent,
                task.querySelector('.task-description').textContent,
                task.querySelector('.task-category').textContent
            ].join(' ').toLowerCase();
            
            if (!searchableContent.includes(search)) {
                show = false;
            }
        }

        // Tampilkan atau sembunyikan task
        task.style.display = show ? '' : 'none';
    });

    // Update counter dan progress setelah filter
    updateTaskCount();
}

// Fungsi untuk mengatur task ke kiri
function arrangeTasksToLeft() {
    const taskContainer = document.querySelector('.row');
    const visibleTasks = document.querySelectorAll('.task-card[style="display: "]');
    
    // Reset container
    taskContainer.style.justifyContent = 'flex-start';
    
    // Atur posisi setiap task
    visibleTasks.forEach((task, index) => {
        const column = task.closest('.col-md-4');
        if (column) {
            column.style.order = index;
        }
    });
}

// Fungsi untuk menampilkan/menyembunyikan task berdasarkan filter
function updateTaskVisibility() {
    const tasks = document.querySelectorAll('.task-card');
    const priority = document.querySelector('select[name="filter_priority"]').value;
    const category = document.querySelector('select[name="filter_category"]').value;
    const date = document.querySelector('input[name="filter_date"]').value;
    const search = document.querySelector('input[name="search"]').value.toLowerCase();

    tasks.forEach(task => {
        let show = true;

        // Logika filter prioritas
        if (priority) {
            const taskPriority = task.classList.contains('high-priority') ? '3' : 
                               task.classList.contains('medium-priority') ? '2' : 
                               task.classList.contains('low-priority') ? '1' : '';
            
            if (taskPriority !== priority) {
                show = false;
            }
        }

        // Filter berdasarkan kategori
        if (category && task.querySelector('.task-category').textContent.trim() !== category) {
            show = false;
        }

        // Filter berdasarkan tanggal
        if (date) {
            const taskDate = task.querySelector('.task-meta-item:first-child').textContent.trim();
            if (!taskDate.includes(date)) {
                show = false;
            }
        }

        // Filter berdasarkan pencarian
        if (search) {
            const taskText = task.textContent.toLowerCase();
            if (!taskText.includes(search)) {
                show = false;
            }
        }

        // Tampilkan atau sembunyikan task
        task.style.display = show ? '' : 'none';
    });

    // Update jumlah task yang ditampilkan
    updateTaskCount();
}

// Fungsi untuk mengupdate jumlah task yang ditampilkan
function updateTaskCount() {
    const total = document.querySelectorAll('.task-card').length;
    const completed = document.querySelectorAll('.task-card .task-meta-item i.fa-check-circle').length;
    const pending = total - completed;

    // Update statistik
    const totalCounter = document.querySelector('.total .stat-number');
    const pendingCounter = document.querySelector('.pending .stat-number');
    const completedCounter = document.querySelector('.completed .stat-number');

    if (totalCounter) totalCounter.textContent = total;
    if (pendingCounter) pendingCounter.textContent = pending;
    if (completedCounter) completedCounter.textContent = completed;

    // Update progress bar
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar && total > 0) {
        const progress = (completed / total) * 100;
        progressBar.style.width = progress + '%';
        progressBar.setAttribute('aria-valuenow', progress);
        const percentage = progressBar.querySelector('.progress-percentage');
        if (percentage) {
            percentage.textContent = Math.round(progress) + '%';
        }
    }
}

// Fungsi reset filter yang baru
function resetAllFilters() {
    // Reset form inputs
    const filterForm = document.querySelector('form');
    const priority = document.querySelector('select[name="filter_priority"]');
    const category = document.querySelector('select[name="filter_category"]');
    const date = document.querySelector('input[name="filter_date"]');
    const search = document.querySelector('input[name="search"]');
    const filterStatus = document.querySelector('input[name="filter_status"]');

    // Reset nilai filter
    if (priority) priority.value = '';
    if (category) category.value = '';
    if (date) date.value = '';
    if (search) search.value = '';
    if (filterStatus) filterStatus.value = '';

    // Reset URL dan tampilan
    const baseUrl = window.location.pathname;
    const cleanUrl = new URL(baseUrl, window.location.origin);
    
    // Simpan state ke history tanpa parameter
    window.history.pushState({}, '', cleanUrl);

    // Perbarui tampilan task
    const tasks = document.querySelectorAll('.task-card');
    tasks.forEach(task => {
        task.style.display = ''; // Tampilkan semua task
    });

    // Reset status aktif di menu filter
    const menuButtons = document.querySelectorAll('.menu-btn');
    menuButtons.forEach(btn => {
        btn.classList.remove('active');
    });

    // Reset counter statistik
    updateTaskCount();

    // Refresh halaman untuk memastikan semua perubahan diterapkan
    window.location.reload();
}

// Event listeners untuk filter
document.addEventListener('DOMContentLoaded', function() {
    // Tambahkan event listeners untuk setiap input filter
    const filterInputs = document.querySelectorAll('select[name^="filter_"], input[name="filter_date"], input[name="search"]');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            updateTaskVisibility();
        });
    });

    // Event listener untuk input pencarian
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            updateTaskVisibility();
        }, 300));
    }

    // Initialize filter state
    updateTaskVisibility();

    const resetButton = document.querySelector('button[type="button"]');
    if (resetButton) {
        resetButton.addEventListener('click', function(e) {
            e.preventDefault();
            resetAllFilters();
        });
    }
});

// Utility function untuk debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Event listener untuk window resize
window.addEventListener('resize', debounce(() => {
    arrangeTasksToLeft();
}, 250));

// Fungsi untuk menginisialisasi dan mengupdate progress bar
function initProgressBar() {
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        const targetWidth = progressBar.getAttribute('data-width');
        
        // Set width awal ke 0
        progressBar.style.width = '0%';
        
        // Berikan delay kecil sebelum animasi
        setTimeout(() => {
            // Animasikan ke target width
            progressBar.style.width = targetWidth + '%';
            
            // Update teks persentase
            const percentage = progressBar.querySelector('.progress-percentage');
            if (percentage) {
                percentage.textContent = Math.round(targetWidth) + '%';
                percentage.style.opacity = '1';
            }
            
            // Tambahkan class untuk menunjukkan progress
            progressBar.classList.add('progress-animated');
        }, 300);
    }
}

// Update fungsi updateTaskCount untuk memperbarui progress bar
function updateTaskCount() {
    const total = document.querySelectorAll('.task-card').length;
    const completed = document.querySelectorAll('.task-card .task-meta-item i.fa-check-circle').length;
    const pending = total - completed;

    // Update statistik
    const totalCounter = document.querySelector('.total .stat-number');
    const pendingCounter = document.querySelector('.pending .stat-number');
    const completedCounter = document.querySelector('.completed .stat-number');

    if (totalCounter) totalCounter.textContent = total;
    if (pendingCounter) pendingCounter.textContent = pending;
    if (completedCounter) completedCounter.textContent = completed;

    // Update progress bar secara real-time
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar && total > 0) {
        const progress = (completed / total) * 100;
        progressBar.style.width = progress + '%';
        progressBar.setAttribute('data-width', progress);
        progressBar.setAttribute('aria-valuenow', progress);
        
        const percentage = progressBar.querySelector('.progress-percentage');
        if (percentage) {
            percentage.textContent = Math.round(progress) + '%';
            percentage.style.opacity = '1';
        }
    }
}

// Panggil initProgressBar saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    initProgressBar();
});

