export default {
  data() {
    return {
      activeLocale: null,
    };
  },

  beforeMount() {
    Nova.$on('nova-translatable@setAllLocale', this.setActiveLocale);
  },

  methods: {
    getFieldLocales(field) {
      let localeKeys = Object.keys(field.translatable.locales);

      if (field.translatable.prioritize_nova_locale) {
        localeKeys = localeKeys.sort((a, b) => {
          if (a === Nova.config.locale && b !== Nova.config.locale) return -1;
          if (a !== Nova.config.locale && b === Nova.config.locale) return 1;
          return 0;
        });
      }

      return localeKeys.map(key => ({ key, name: field.translatable.locales[key] }));
    },

    setAllLocales(newLocale) {
      Nova.$emit('nova-translatable@setAllLocale', newLocale);
    },

    setActiveLocale(newLocale) {
      this.activeLocale = newLocale;
    },
  },
};
