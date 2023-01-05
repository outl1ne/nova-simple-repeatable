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
      if (!this.currentPageCount) {
        return null;
      }

      const first = this.perPage * (this.currentPage - 1);
      const from = Nova.formatNumber(first + 1);
      const to = Nova.formatNumber(first + this.currentPageCount);
      const total = Nova.formatNumber(this.currentField.totalCount);

      return `${from}-${to} ${this.__('of')} ${total}`
    },
  },
};
