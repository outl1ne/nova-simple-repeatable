<template>
  <default-field :field="field" :errors="errors" :show-help-text="showHelpText" class="simple-repeatable">
    <template slot="field">
      <div class="flex flex-col">
        <!-- Title columns -->
        <div class="simple-repeatable-header-row flex border-b border-40 py-2">
          <div v-for="(rowField, i) in rows[0]" :key="i" class="font-bold text-90 text-md w-full ml-3 flex">
            {{ rowField.name }}

            <!--  If field is nova-translatable, render seperate locale-tabs   -->
            <nova-translatable-locale-tabs
              style="padding: 0"
              class="ml-auto"
              v-if="rowField.component === 'translatable-field'"
              :locales="getFieldLocales(rowField.translatable.locales)"
              :active-locale="activeLocale || getFieldLocales(rowField.translatable.locales)[0].key"
              @tabClick="setAllLocales"
              @dblclick="setAllLocales"
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
                :errors="repeatableErrors[i]"
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
          class="add-button btn btn-default btn-primary mt-3"
          :class="{ 'delete-width': canDeleteRows }"
          type="button"
        >
          {{ __('simpleRepeatable.addRow') }}
        </button>
      </div>
    </template>
  </default-field>
</template>

<script>
import Draggable from 'vuedraggable';
import { Errors } from 'form-backend-validation';
import { FormField, HandlesValidationErrors } from 'laravel-nova';
import NovaTranslatableSupport from '../mixins/NovaTranslatableSupport';

let UNIQUE_ID_INDEX = 0;

export default {
  mixins: [FormField, HandlesValidationErrors, NovaTranslatableSupport],

  components: { Draggable },

  props: ['resourceName', 'resourceId', 'field'],

  data() {
    return {
      rows: [],
    };
  },

  methods: {
    setInitialValue() {
      this.rows = this.field.rows.map(row => this.copyFields(row.fields));

      // Initialize minimum amount of rows
      if (this.field.minRows && !isNaN(this.field.minRows)) {
        while (this.rows.length < this.field.minRows) this.addRow();
      }
    },

    copyFields(fields) {
      // Return an array of fields with unique attribute
      return fields.map(field => ({
        ...field,
        attribute: `${field.attribute}---${UNIQUE_ID_INDEX++}`,
        value: field.value,
      }));
    },

    fill(formData) {
      const allValues = [];

      for (const row of this.rows) {
        let formData = new FormData();
        const rowValues = {};

        // Fill formData with field values
        row.forEach(field => field.fill(formData));

        // Save field values to rowValues
        for (const item of formData) {
          const normalizedAttribute = item[0].replace(/---\d+/, '');
          rowValues[normalizedAttribute] = item[1];
        }

        allValues.push(rowValues);
      }

      formData.append(this.field.attribute, JSON.stringify(allValues));
    },

    addRow() {
      this.rows.push(this.copyFields(this.field.fields));
    },

    deleteRow(index) {
      this.rows.splice(index, 1);
    },
  },

  computed: {
    repeatableErrors() {
      const errorKeys = Object.keys(this.errors.errors).filter(key => key.startsWith(this.field.attribute));
      const uniqueErrorKeyMatches = [];
      errorKeys.forEach(key => {
        const match = key.match(/.+.(\d.)/)[0];
        if (!uniqueErrorKeyMatches.find(key => key.startsWith(match))) uniqueErrorKeyMatches.push(match);
      });

      const errors = {};
      uniqueErrorKeyMatches.forEach(keyMatch => {
        const keyWithoutPrefix = keyMatch.slice(this.field.attribute.length + 1);
        const fieldIndex = keyWithoutPrefix.match(/(\d)./)[1];

        const errorKeysForThisIndex = errorKeys.filter(key => key.startsWith(keyMatch));

        const errorsForThisIndex = {};
        errorKeysForThisIndex.forEach(errorKey => {
          const fieldName = errorKey.slice(keyMatch.length);
          errorsForThisIndex[fieldName] = this.errors.errors[errorKey];
        });
        errors[fieldIndex] = new Errors(errorsForThisIndex);
      });

      return errors;
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
.simple-repeatable {
  .simple-repeatable-header-row {
    width: 100%;
  }

  .simple-repeatable-row {
    width: calc(100% + 68px);

    > .simple-repeatable-fields-wrapper {
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
