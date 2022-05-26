<template>
  <default-field :field="field" :errors="errors" :show-help-text="showHelpText" class="simple-repeatable form-field">
    <template slot="field">
      <div class="flex flex-col">
        <!-- Title columns -->
        <div v-if="rows.length" class="simple-repeatable-header-row flex border-b border-40 py-2">
          <div v-for="(rowField, i) in fields" :key="i" class="font-bold text-90 text-md w-full ml-3 flex">
            {{ rowField.name }}

            <!--  If field is nova-translatable, render seperate locale-tabs   -->
            <nova-translatable-locale-tabs
              style="padding: 0"
              class="ml-auto"
              v-if="rowField.component === 'translatable-field'"
              :locales="rowField.formattedLocales"
              :display-type="rowField.translatable.display_type"
              :active-locale="activeLocales[i] || rowField.formattedLocales[0].key"
              :locales-with-errors="repeatableValidation.locales[rowField.originalAttribute]"
              @tabClick="locale => setAllLocales(`sr-${field.attribute}-${rowField.originalAttribute}`, locale)"
              @doubleClick="locale => setAllLocales(void 0, locale)"
            />
          </div>
        </div>

        <draggable v-model="rows" handle=".vue-draggable-handle">
          <div
            v-for="(row, i) in rows"
            :key="row[0].attribute"
            class="simple-repeatable-row flex py-3 pl-3 relative rounded-md"
          >
            <div class="vue-draggable-handle flex justify-center items-center cursor-pointer">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" class="fill-current">
                <path
                  d="M4 5h16a1 1 0 0 1 0 2H4a1 1 0 1 1 0-2zm0 6h16a1 1 0 0 1 0 2H4a1 1 0 0 1 0-2zm0 6h16a1 1 0 0 1 0 2H4a1 1 0 0 1 0-2z"
                />
              </svg>
            </div>

            <div class="simple-repeatable-fields-wrapper w-full flex">
              <component
                v-for="(rowField, j) in row"
                :key="j"
                :is="`form-${rowField.component}`"
                :field="rowField"
                :errors="repeatableValidation.errors"
                :unique-id="getUniqueId(field, rowField)"
                class="mr-3"
              />
            </div>

            <div
              class="delete-icon flex justify-center items-center cursor-pointer"
              @click="deleteRow(i)"
              v-if="canDeleteRows"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" class="fill-current">
                <path
                  d="M8 6V4c0-1.1.9-2 2-2h4a2 2 0 012 2v2h5a1 1 0 010 2h-1v12a2 2 0 01-2 2H6a2 2 0 01-2-2V8H3a1 1 0 110-2h5zM6 8v12h12V8H6zm8-2V4h-4v2h4zm-4 4a1 1 0 011 1v6a1 1 0 01-2 0v-6a1 1 0 011-1zm4 0a1 1 0 011 1v6a1 1 0 01-2 0v-6a1 1 0 011-1z"
                />
              </svg>
            </div>
          </div>
        </draggable>

        <button
          v-if="canAddRows"
          @click="addRow"
          class="add-button btn btn-default btn-primary"
          :class="{ 'delete-width': canDeleteRows, 'mt-3': rows.length }"
          type="button"
        >
          {{ field.addRowLabel }}
        </button>
      </div>
    </template>
  </default-field>
</template>

<script>
import Draggable from 'vuedraggable';
import { Errors } from 'form-backend-validation';
import { FormField, HandlesValidationErrors } from 'laravel-nova';
import HandlesRepeatable from '../mixins/HandlesRepeatable';

export default {
  mixins: [FormField, HandlesValidationErrors, HandlesRepeatable],

  components: { Draggable },

  props: ['resourceName', 'resourceId', 'field'],

  methods: {
    fill(formData) {
      const ARR_REGEX = () => /\[\d+\]$/g;
      const allValues = [];

      for (const row of this.rows) {
        let formData = new FormData();
        const rowValues = {};

        // Fill formData with field values
        row.forEach(field => field.fill(formData));

        // Save field values to rowValues
        for (const item of formData) {
          let normalizedValue = null;

          let key = item[0];
          if (key.split('---').length === 3) {
            key = key.split('---').slice(1).join('---');
          }
          key = key.replace(/---\d+/, '');

          // Is key is an array, we need to remove the '.en' part from '.en[0]'
          const isArray = !!key.match(ARR_REGEX());
          if (isArray) {
            const result = ARR_REGEX().exec(key);
            key = `${key.slice(0, result.index)}${key.slice(result.index + result[0].length)}`;
          }

          try {
            // Attempt to parse value
            normalizedValue = JSON.parse(item[1]);
          } catch (e) {
            // Value is already a valid string
            normalizedValue = item[1];
          }

          if (isArray) {
            if (!rowValues[key]) rowValues[key] = [];
            rowValues[key].push(normalizedValue);
          } else {
            rowValues[key] = normalizedValue;
          }
        }

        allValues.push(rowValues);
      }

      formData.append(this.field.attribute, JSON.stringify(allValues));
    },

    addRow() {
      this.rows.push(this.copyFields(this.field.fields, this.rows.length));
    },

    deleteRow(index) {
      this.rows.splice(index, 1);
    },
  },

  computed: {
    repeatableValidation() {
      const fields = this.fields;
      const errors = this.errors.errors;
      const repeaterAttr = this.field.attribute;
      const safeRepeaterAttr = this.field.attribute.replace(/.{16}__/, '');
      const erroredFieldLocales = {};
      const formattedKeyErrors = {};

      // Find errored locales
      for (const field of fields) {
        const fieldAttr = field.originalAttribute;

        // Find all errors related to this field
        const relatedErrors = Object.keys(errors).filter(
          err => !!err.match(new RegExp(`^${safeRepeaterAttr}.\\d+.${fieldAttr}`))
        );

        const isTranslatable = field.component === 'translatable-field';
        if (isTranslatable) {
          const foundLocales = relatedErrors.map(errorKey => errorKey.split('.').slice(-1)).flat();
          erroredFieldLocales[fieldAttr] = foundLocales;
        }

        // Format field
        relatedErrors.forEach(errorKey => {
          const rowIndex = errorKey.split('.')[1];
          let uniqueKey = `${repeaterAttr}---${field.originalAttribute}---${rowIndex}`;

          if (isTranslatable) {
            const locale = errorKey.split('.').slice(-1)[0];
            uniqueKey = `${uniqueKey}.${locale}`;
          }

          formattedKeyErrors[uniqueKey] = errors[errorKey];
        });
      }

      return {
        errors: new Errors(formattedKeyErrors),
        locales: erroredFieldLocales,
      };
    },

    canAddRows() {
      if (!this.field.canAddRows) return false;
      if (!!this.field.maxRows) return this.rows.length < this.field.maxRows;
      return true;
    },

    canDeleteRows() {
      if (!this.field.canDeleteRows) return false;
      if (!!this.field.minRows) return this.rows.length > this.field.minRows;
      return true;
    },
  },
};
</script>

<style lang="scss">
.simple-repeatable.form-field {
  .simple-repeatable-header-row {
    width: 100%;
  }

  .simple-repeatable-row {
    width: calc(100% + 68px);

    > .simple-repeatable-fields-wrapper {
      > *,
        // Improve compatibility with nova-translatable
      .translatable-field > div:not(:first-child) > div {
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

    margin-left: -46px;

    .delete-icon {
      width: 36px;
      height: 36px;
      margin-right: 10px;
      cursor: pointer;

      &:hover {
        cursor: pointer;

        > svg > path {
          fill: var(--danger);
        }
      }
    }

    .vue-draggable-handle {
      height: 36px;
      width: 36px;
      margin-right: 10px;

      &:hover {
        opacity: 0.8;
      }
    }

    &:hover {
      background: var(--40);
    }
  }

  .add-button {
    width: calc(100% + 11px);

    &.delete-width {
      width: calc(100% - 22px);
    }
  }

  > :nth-child(1) {
    min-width: 20%;
  }

  // Make field area full width
  > :nth-child(2) {
    width: 100% !important;
    margin-right: 24px;
  }

  // Compact theme support
  > *:only-child {
    > *:nth-child(1) {
      min-width: 20%;
    }

    // Make field area full width
    > *:nth-child(2) {
      width: 100% !important;
      margin-right: 24px;
    }
  }
}
</style>
