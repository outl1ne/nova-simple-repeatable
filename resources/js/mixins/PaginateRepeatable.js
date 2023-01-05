export default {
  computed: {
    currentPage() {
      return this.currentField.currentPage || 1;
    },

    isPaginated() {
      return this.currentField.isPaginated || false;
    },

    perPage() {
      return this.currentField.perPage;
    },

    totalPages() {
      return this.currentField.totalCount ? Math.ceil(this.currentField.totalCount / this.perPage) : 0;
    },

    currentPageCount() {
      return this.currentField.currentPageCount || 0;
    },

    resultCountLabel() {
      const first = this.perPage * (this.currentPage - 1);

      return (
        this.currentPageCount &&
        `${Nova.formatNumber(first + 1)}-${Nova.formatNumber(first + this.currentPageCount)} ${this.__(
          'of'
        )} ${Nova.formatNumber(this.totalPages)}`
      );
    },
  },
};
