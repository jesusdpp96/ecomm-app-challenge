// Table sorting functionality
class TableSorter {
  constructor(tableId) {
      this.table = document.getElementById(tableId);
      this.headers = this.table ? this.table.querySelectorAll('th[data-sort]') : [];
      this.currentSort = { column: null, direction: 'asc' };
      this.init();
  }
  
  init() {
      this.headers.forEach(header => {
          header.addEventListener('click', () => {
              const sortBy = header.getAttribute('data-sort');
              this.sort(sortBy, header);
          });
      });
  }
  
  sort(column, headerEl) {
      const tbody = this.table.querySelector('tbody');
      const rows = Array.from(tbody.querySelectorAll('tr'));
      
      // Determine sort direction
      let direction = 'asc';
      if (this.currentSort.column === column && this.currentSort.direction === 'asc') {
          direction = 'desc';
      }
      
      // Update visual indicators
      this.headers.forEach(h => {
          h.classList.remove('sort-asc', 'sort-desc');
      });
      headerEl.classList.add(`sort-${direction}`);
      
      // Sort rows
      rows.sort((a, b) => {
          const aValue = this.getCellValue(a, column);
          const bValue = this.getCellValue(b, column);
          
          let comparison = 0;
          if (column === 'price' || column === 'id') {
              comparison = parseFloat(aValue) - parseFloat(bValue);
          } else {
              comparison = aValue.localeCompare(bValue);
          }
          
          return direction === 'desc' ? -comparison : comparison;
      });
      
      // Re-append sorted rows
      rows.forEach(row => tbody.appendChild(row));
      
      this.currentSort = { column, direction };
  }
  
  getCellValue(row, column) {
      const columnIndex = Array.from(this.headers).findIndex(h => h.getAttribute('data-sort') === column);
      const cell = row.cells[columnIndex];
      return cell ? cell.textContent.trim() : '';
  }
}