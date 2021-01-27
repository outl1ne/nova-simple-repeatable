export default {
  data() {
    return {
      activeLocale: 'en',
    };
  },

  beforeMount() {
    Nova.$on('nova-translatable@setAllLocale', this.setActiveLocale);
  },

  methods: {
    getFieldLocales(locales) {
      return Object.keys(locales)
        .sort((a, b) => {
          if (a === Nova.config.locale && b !== Nova.config.locale) return -1;
          if (a !== Nova.config.locale && b === Nova.config.locale) return 1;
          return 0;
        })
        .map(key => ({ key, name: locales[key] }));
    },

    setAllLocales(newLocale) {
      Nova.$emit('nova-translatable@setAllLocale', newLocale);
    },

    setActiveLocale(newLocale) {
      this.activeLocale = newLocale;
    },
  },
};
