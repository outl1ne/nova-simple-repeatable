<template>
  <PanelItem :index="index" :field="field" class="simple-repeatable detail-field">
    <template #value>
      <div
        class="nsr-overflow-hidden nsr-relative nsr-rounded-lg nsr-shadow nsr-border nsr-border-slate-200 dark:nsr-border-slate-600 bg-white dark:nsr-bg-slate-800"
        v-if="values && values.length"
      >
        <table class="nsr-table nsr-w-full nsr-table-default nova-resource-table">
          <thead>
            <tr class="nsr-border-b nsr-border-slate-200 dark:nsr-border-slate-600">
              <th v-for="(header, i) in headers" :key="i">{{ header.name }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(row, i) of field.rows"
              :key="i"
              class="simple-repeatable-table-row odd:nsr-bg-slate-50 hover:nsr-bg-slate-100 dark:odd:nsr-bg-slate-700 dark:hover:nsr-bg-slate-600"
            >
              <td
                class="nsr-font-mono nsr-text-sm simple-repeatable-detail-field-wrapper"
                style="height: 2rem"
                v-for="(rowField, j) in row.fields"
                :key="j"
              >
                <component
                  :key="j"
                  :is="`detail-${rowField.component}`"
                  :field="rowField"
                  class="nsr-mr-3"
                  :unique-id="getUniqueId(field, rowField)"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else>{{ (value && value.label) || '—' }}</div>
    </template>
  </PanelItem>
</template>

<script>
import HandlesRepeatable from '../mixins/HandlesRepeatable';

export default {
  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],

  mixins: [HandlesRepeatable],

  computed: {
    values() {
      let value = this.field.value;
      if (!value) return void 0;

      if (Array.isArray(value)) return this.handleValue(value);

      try {
        value = JSON.parse(value);
      } catch (e) {
        return void 0;
      }
      if (!Array.isArray(value)) return void 0;

      return this.handleValue(value);
    },

    headers() {
      const rows = this.field.rows;
      return rows && rows[0] && rows[0].fields.map(field => ({ name: field.name, attribute: field.attribute }));
    },
  },

  methods: {
    handleValue(valuesArray) {
      const fields = this.field.fields;

      const fieldsWithOptions = fields.filter(field => Array.isArray(field.options) && !!field.options.length);
      if (fieldsWithOptions.length > 0) {
        fieldsWithOptions.forEach(field => {
          valuesArray = valuesArray.map(entry => {
            const id = entry[field.attribute];

            if (!!id) {
              // Try the usual label-value pair
              if (field.options[0].value && field.options[0].label) {
                // Yay, we recognize this format
                const optionMatch = field.options.find(o => String(o.value) === String(id));
                entry[field.attribute] = optionMatch ? optionMatch.label : id;
              }

              // Also try the key-value pair format
              else if (!!field.options[id]) {
                entry[field.attribute] = field.options[id];
              }
            }
            return entry;
          });
        });
      }

      return this.sortValueByHeaders(valuesArray);
    },

    sortValueByHeaders(valuesArray) {
      const headers = this.headers;

      const newArray = [];
      valuesArray.forEach(entry => {
        const temp = [];
        headers.forEach(header => {
          temp.push(entry[header.attribute]);
        });
        newArray.push(temp);
      });

      return newArray;
    },
  },
};
</script>

<style lang="scss">
.simple-repeatable.detail-field {
  .simple-repeatable-detail-field-wrapper {
    > *,
        // Improve compatibility with nova-translatable
      .translatable-field > div:not(:first-child) div {
      flex: 1;
      flex-shrink: 0;
      min-width: 0;
      border: none !important;

      // Hide name
      > *:nth-child(1):not(:only-child) {
        display: none;
      }

      > *:only-child {
        > *:nth-child(1):not(:only-child) {
          display: none;
        }

        > :nth-child(2) {
          width: 100% !important;
          padding: 0 !important;
        }
      }

      // Improve compatibility with nova-compact-theme
      .compact-nova-field-wrapper {
        padding: 0 !important;
      }

      // Fix field width and padding
      > :nth-child(2) {
        width: 100% !important;
        padding: 0 !important;
      }
    }
  }

  .simple-repeatable-table-row {
    td {
      padding: 4px 8px;
    }
  }
}
</style>
