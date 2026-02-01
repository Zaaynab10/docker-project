
document.addEventListener('DOMContentLoaded', function() {
    // Animate elements on creation
    const taskItems = document.querySelectorAll('.task-item');
    taskItems.forEach((item, index) => {
        item.style.animation = `fadeInUp 0.3s ease ${index * 0.05}s both`;
    });

    // Drag and Drop functionality - Fixed to prevent duplication
    let draggedElement = null;
    let dropHandled = false; // Flag to prevent multiple drop events

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
                dropHandled = false; // Reset flag on new drag
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
                dropHandled = false;

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

            // Drop - Fixed version
            list.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('drag-over');

                // Prevent multiple drop handling
                if (dropHandled || !draggedElement) return;
                dropHandled = true;

                const currentSection = draggedElement.closest('.task-section');
                const targetSection = this.closest('.task-section');
                const taskId = draggedElement.dataset.id;

                // If no sections found, exit
                if (!currentSection || !targetSection) {
                    return;
                }

                const currentStatus = currentSection.dataset.status;
                const targetStatus = targetSection.dataset.status;

                // Get CSRF token from the target list's form
                const csrfInput = targetSection.querySelector('input[name="csrf_token"]') ||
                                 this.querySelector('input[name="csrf_token"]');
                const csrfToken = csrfInput ? csrfInput.value : '';

                // Get new order based on position in target list
                const taskItemsInTarget = [...this.querySelectorAll('.task-item:not(.dragging)')];
                const newOrder = taskItemsInTarget.findIndex(item => item.dataset.id === taskId);

                // If different sections, MOVE the task (change status)
                if (currentStatus !== targetStatus) {
                    console.log('Different section - moving task:', taskId);

                    // Calculate new order before any DOM manipulation
                    const calculatedNewOrder = newOrder >= 0 ? newOrder : 0;

                    // Send request to server FIRST
                    fetch('?action=move', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id=' + encodeURIComponent(taskId) +
                              '&order=' + encodeURIComponent(calculatedNewOrder) +
                              '&status=' + encodeURIComponent(targetStatus) +
                              '&csrf_token=' + encodeURIComponent(csrfToken)
                    }).then(response => {
                        if (response.ok) {
                            console.log('Move successful, reloading...');
                            // Only reload after successful server response
                            location.reload();
                        } else {
                            console.error('Move failed on server');
                            dropHandled = false;
                        }
                    }).catch(err => {
                        console.error('Move error:', err);
                        dropHandled = false;
                    });
                }
                // Same section - reorder only
                else if (currentStatus === targetStatus) {
                    console.log('Same section - reordering');

                    // Calculate position to insert
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
                    
                    // Save order to server after visual reorder
                    setTimeout(() => saveTaskOrder(this), 50);
                }

                // Reset after a short delay to allow for next drag
                setTimeout(() => {
                    dropHandled = false;
                }, 100);
            });
        });
    }

    // Save task order
    function saveTaskOrder(taskList) {
        const taskIds = [];
        const status = taskList.closest('.task-section').dataset.status || 'pending';
        
        taskList.querySelectorAll('.task-item').forEach((item, index) => {
            taskIds.push(item.dataset.id);
        });
        
        console.log('Saving order:', { status: status, ids: taskIds });
        
        // Get CSRF token from the form in this list
        const csrfInput = taskList.querySelector('input[name="csrf_token"]');
        const csrfToken = csrfInput ? csrfInput.value : '';
        
        fetch('?action=reorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'task_ids=' + encodeURIComponent(JSON.stringify(taskIds)) + 
                  '&status=' + encodeURIComponent(status) + 
                  '&csrf_token=' + encodeURIComponent(csrfToken)
        }).then(response => {
            console.log('Reorder response:', response.status);
        }).catch(err => console.error('Reorder error:', err));
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

    // Prevent double submission on task form
    const taskForm = document.querySelector('.task-form');
    if (taskForm) {
        taskForm.addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                // Disable button immediately to prevent double submission
                btn.disabled = true;
                btn.dataset.originalText = btn.textContent;
                btn.textContent = 'Adding...';
            }
        });
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

