$(document).ready(function () {
  const $select = $('#projet_employes');
  if ($select.length) {
    $select.select2({
      placeholder: "Inviter des membres",
      allowClear: true,
      width: '100%'
    });
  }
});

document.querySelectorAll('.task-card').forEach(card => {
    card.setAttribute('draggable', true);

    card.addEventListener('dragstart', e => {
        e.dataTransfer.setData('text/plain', card.dataset.id);
    });
});

document.querySelectorAll('.kanban-column').forEach(column => {
    column.addEventListener('dragover', e => {
        e.preventDefault(); // indispensable
    });

    column.addEventListener('drop', e => {
        e.preventDefault();

        const taskId = e.dataTransfer.getData('text/plain');
        const newStatus = column.dataset.status;

        fetch(`/tasks/${taskId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ status: newStatus })
        });

        const card = document.querySelector(`.task-card[data-id="${taskId}"]`);
        column.appendChild(card);
    });
});

