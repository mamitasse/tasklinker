$(document).ready(function () {
  /* ============================
   * 1) Select2 (prototype)
   * ============================ */
  if ($.fn.select2) {
    // recommandation: classe dédiée
    $('.js-select2').select2({
      width: '100%',
      placeholder: '',
      allowClear: true
    });

    // compat prototype
    $('#projet_employes').select2({ width: '100%' });

    // compat tâches (selon tes ids réels)
    $('#tache_employe, #tache_statut, #task_assignee, #task_status').select2({ width: '100%' });
  }

  /* ============================
   * 2) Drag & Drop Kanban (tâches)
   * ============================ */
  const taskCards = document.querySelectorAll('.task-card');
  const columns = document.querySelectorAll('.kanban-column');

  // Si on n'est pas sur la page kanban => on sort
  if (taskCards.length === 0 || columns.length === 0) {
    return;
  }

  taskCards.forEach(card => {
    card.setAttribute('draggable', 'true');

    card.addEventListener('dragstart', e => {
      e.dataTransfer.setData('text/plain', card.dataset.id);
    });
  });

  columns.forEach(column => {
    column.addEventListener('dragover', e => {
      e.preventDefault();
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
      if (card) {
        column.appendChild(card);
      }
    });
  });
});

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.js-confirm-archive').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();

      const ok = confirm("Confirmer l’archivage de ce projet ?");
      if (!ok) return;

      const formId = btn.dataset.formId;
      const form = document.getElementById(formId);

      if (form) {
        form.submit();
      } else {
        console.error("Formulaire introuvable :", formId);
      }
    });
  });
});
