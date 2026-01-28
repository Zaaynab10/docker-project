
document.addEventListener('DOMContentLoaded', function() {
    // Animate elements on creation
    const taskItems = document.querySelectorAll('.task-item');
    taskItems.forEach((item, index) => {
        item.style.animation = `fadeInUp 0.3s ease ${index * 0.05}s both`;
    });

    // Drag and Drop functionality - Simple and Robust
    let draggedElement = null;

    // Add drag events to all task items
    function initDragAndDrop() {
        const taskItems = document.querySelectorAll('.task-item');
        const taskLists = document.querySelectorAll('.tasks-list');

        taskItems.forEach(item => {
            // Make item draggable
            item.setAttribute('draggable', 'true');

            // Drag start
            item.addEventListener('dragstart', function(e) {
                draggedElement = this;
                this.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', this.dataset.id);
                
                // Make drag handle invisible during drag
                const handle = this.querySelector('.drag-handle');
                if (handle) {
                    handle.style.opacity = '0.3';
                }
            });

            // Drag end
            item.addEventListener('dragend', function(e) {
                this.classList.remove('dragging');
                const handle = this.querySelector('.drag-handle');
                if (handle) {
                    handle.style.opacity = '';
                }
                draggedElement = null;

                // Remove all drag-over classes
                taskLists.forEach(list => list.classList.remove('drag-over'));
            });
        });

        // Add drop zone events to lists
        taskLists.forEach(list => {
            // Drag over - allow dropping
            list.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                this.classList.add('drag-over');
            });

            // Drag enter
            list.addEventListener('dragenter', function(e) {
                e.preventDefault();
                this.classList.add('drag-over');
            });

            // Drag leave
            list.addEventListener('dragleave', function(e) {
                if (e.target === this || e.target.classList.contains('tasks-list')) {
                    this.classList.remove('drag-over');
                }
            });

            // Drop
            list.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('drag-over');

                if (!draggedElement) return;

                const currentSection = draggedElement.closest('.task-section');
                const targetSection = this.closest('.task-section');
                const taskId = draggedElement.dataset.id;

                console.log('Drop - Current:', currentSection?.querySelector('.section-title')?.textContent);
                console.log('Drop - Target:', targetSection?.querySelector('.section-title')?.textContent);

                // If different sections, toggle status
                if (currentSection && targetSection && currentSection !== targetSection) {
                    console.log('Different section - toggling task:', taskId);
                    
                    fetch('?action=toggle', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id=' + taskId
                    }).then(response => {
                        console.log('Toggle response:', response.status);
                        if (response.ok) {
                            location.reload();
                        }
                    }).catch(err => console.error('Toggle error:', err));
                }
                // Same section - reorder
                else if (currentSection && targetSection && currentSection === targetSection) {
                    console.log('Same section - reordering');
                    
                    // Get position relative to other items
                    const listItems = [...this.querySelectorAll('.task-item:not(.dragging)')];
                    const mouseY = e.clientY;
                    
                    let insertBeforeElement = null;
                    
                    for (const listItem of listItems) {
                        const box = listItem.getBoundingClientRect();
                        const boxCenter = box.top + box.height / 2;
                        
                        if (mouseY < boxCenter) {
                            insertBeforeElement = listItem;
                            break;
                        }
                    }
                    
                    if (insertBeforeElement) {
                        this.insertBefore(draggedElement, insertBeforeElement);
                    } else {
                        this.appendChild(draggedElement);
                    }
                    
                    // Save order (optional)
                    saveTaskOrder();
                }

                draggedElement = null;
            });
        });
    }

    // Save task order
    function saveTaskOrder() {
        const order = [];
        document.querySelectorAll('.task-item').forEach((item, index) => {
            order.push({
                id: item.dataset.id,
                position: index
            });
        });
        console.log('Order saved:', order);
    }

    // Initialize
    initDragAndDrop();

    // Confirmation before deletion
    const deleteButtons = document.querySelectorAll('.task-delete');
    deleteButtons.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this task?')) {
                e.preventDefault();
            }
        });
    });

    // Auto-focus on title field
    const titleInput = document.querySelector('input[name="title"]');
    if (titleInput) {
        titleInput.focus();
    }

    // Disable button if title is empty
    const form = document.querySelector('.task-form');
    const submitBtn = form?.querySelector('button[type="submit"]');
    if (titleInput && submitBtn) {
        titleInput.addEventListener('input', function() {
            submitBtn.disabled = !this.value.trim();
        });
        submitBtn.disabled = !titleInput.value.trim();
    }
});

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);

