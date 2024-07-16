export default {
  data() {
    return {
      rows: [],
      activeLocales: {},
    };
  },

  mounted() {
    this.rows = this.field.rows.map((row, rowIndex) => this.copyFields(row.fields, rowIndex));

    // Listen to active locales (nova-translatable support)
    (this.field.fields || []).forEach((field, i) => {
      if (field.component !== 'translatable-field') return;

      this.activeLocales[i] = void 0;

      const id = field.component === 'translatable-field' ? `sr-${this.field.attribute}-${field.attribute}` : void 0;
      const eventName = this.getAllLocalesEventName(id);
      Nova.$on(eventName, locale => {
        this.activeLocales = {
          ...this.activeLocales,
          [i]: locale,
        };
      });

      Nova.$on(this.getAllLocalesEventName(void 0), locale => {
        this.activeLocales = {
          ...this.activeLocales,
          [i]: locale,
        };
      });
    });
  },

  methods: {
    setInitialValue() {
      // Initialize minimum amount of rows
      if (this.currentField.minRows && !isNaN(this.currentField.minRows)) {
        while (this.rows.length < this.currentField.minRows) this.addRow();
      }
    },

    copyFields(fields, rowIndex = void 0) {
      if (!rowIndex) rowIndex = this.rows.length;

      // Return an array of fields with unique attribute
      return fields.map(field => {
        const uniqueAttribute = `${this.field.attribute}---${field.attribute}---${rowIndex}`;

        let formattedLocales = null;
        if (field.component === 'translatable-field') {
          formattedLocales = this.getFieldLocales(field);
        }

        return {
          ...field,
          dependsOn: this.transformDependsOn(field.dependsOn, rowIndex),
          originalAttribute: field.attribute,
          validationKey: uniqueAttribute,
          attribute: uniqueAttribute,
          formattedLocales,
        };
      });
    },

    transformDependsOn(obj, rowIndex) {
      if (!obj || typeof obj !== 'object') {
        return obj;
      }

      let transformedObj = {};

      Object.keys(obj).forEach(key => {
        let transformedKey = this.transformDependsOnString(key, rowIndex);
        transformedObj[transformedKey] = obj[key];
      });

      return transformedObj;
    },


    // Take backend (dot) format of dependsOn and format for frontend (triple dash)
    transformDependsOnString(inputString, rowIndex) {
      if (!inputString) {
        return null;
      }

      if (!inputString.includes('.')) {
        return inputString;
      }

      let transformedString = inputString.replace(/\./g, '---');
      transformedString += `---${rowIndex}`;

      return transformedString;
    },

    getFieldLocales(field) {
      let localeKeys = Object.keys(field.translatable.locales);

      if (field.translatable.prioritize_nova_locale) {
        localeKeys = localeKeys.sort((a, b) => {
          if (a === Nova.config('locale') && b !== Nova.config('locale')) return -1;
          if (a !== Nova.config('locale') && b === Nova.config('locale')) return 1;
          return 0;
        });
      }

      return localeKeys.map(key => ({ key, name: field.translatable.locales[key] }));
    },

    setAllLocales(uniqueId, newLocale) {
      Nova.$emit(this.getAllLocalesEventName(uniqueId), newLocale);
    },

    setActiveLocale(newLocale) {
      this.activeLocale = newLocale;
    },

    getAllLocalesEventName(uniqueId) {
      const id = uniqueId ?? void 0;
      return id !== void 0 ? `nova-translatable-${id}@setAllLocale` : 'nova-translatable@setAllLocale';
    },

    getUniqueId(field, rowField) {
      const rAttribute = rowField.originalAttribute || rowField.attribute;
      return rowField.component === 'translatable-field' ? `sr-${field.attribute}-${rAttribute}` : void 0;
    },
  },

  computed: {
    fields() {
      return (this.rows[0] || []).filter(field => field.component !== 'hidden-field');
    },
  },
};
