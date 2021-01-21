<template>
  <default-field :field="field" :errors="errors" :show-help-text="showHelpText" class="simple-repeatable">
    <template slot="field">
      <div class="flex flex-col">
        <!-- Title columns -->
        <div class="simple-repeatable-header-row flex border-b border-40 py-2">
          <div v-for="(repField, i) in field.repeatableFields" :key="i" class="font-bold text-90 text-md w-full mr-3">
            {{ repField.name }}
          </div>
        </div>

        <draggable v-model="fieldsWithValues" :options="{ handle: '.vue-draggable-handle' }">
          <div
            v-for="(fields, i) in fieldsWithValues"
            :key="fields[0].attribute"
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
                v-for="(repField, j) in fields"
                :key="j"
                :is="`form-${repField.component}`"
                :field="repField"
                :errors="repeatableErrors[i]"
                class="mr-3"
              />
            </div>

            <div
              v-if="canDeleteRow"
              @click="deleteRow(i)"
              class="delete-icon flex justify-center items-center cursor-pointer"
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
          v-if="canAddRow"
          @click="addRow"
          class="add-button btn btn-default btn-primary mt-3"
          :class="{ 'delete-width': field.canDeleteRows }"
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

let UNIQUE_ID_INDEX = 0;

export default {
  mixins: [FormField, HandlesValidationErrors],

  components: { Draggable },

  props: ['resourceName', 'resourceId', 'field'],
  

  data() {
    return {
      fieldsWithValues: [],
    };
  },

  methods: {
    
    setInitialValue() {
      let value = [];
      try {
        if (!this.field.value) {
          value = [];
        } else if (typeof this.field.value === 'string') {
          value = JSON.parse(this.field.value) || [];
        } else if (Array.isArray(this.field.value)) {
          value = this.field.value;
        }
      } catch (e) {
        value = [];
      }

      this.fieldsWithValues = value.map(this.copyFields);
    },

    copyFields(value) {
      return this.field.repeatableFields.map(field => ({
        ...field,
        attribute: `${field.attribute}---${UNIQUE_ID_INDEX++}`,
        value: value && value[field.attribute],
      }));
    },

    fill(formData) {
      const allValues = [];
      for (const fields of this.fieldsWithValues) {
        const rowValues = {};

        for (const field of fields) {
          const formData = new FormData();
          field.fill(formData);

          const normalizedAttribute = field.attribute.replace(/---\d+/, '');
          rowValues[normalizedAttribute] = formData.get(field.attribute);
        }

        allValues.push(rowValues);
      }

      formData.append(this.field.attribute, JSON.stringify(allValues));
    },

    addRow() {
      this.fieldsWithValues.push(this.copyFields());
    },

    deleteRow(index) {
      this.fieldsWithValues.splice(index, 1);
    },
  },

  mounted() {
    let diff = this.field.minRows - this.fieldsWithValues.length;
    diff = (this.field.minRows > this.field.maxRows) ? this.field.maxRows : diff;
    if (this.fieldsWithValues.length === 0 || diff > 0) {
      for (let i = 0; i < diff; i++) {
        this.addRow();
      }
    }
  },

  computed: {
    row() {
      return {
        rowCount: this.fieldsWithValues.length,
        minRows: this.field.minRows,
        maxRows: this.field.maxRows,
        canAddRows: this.field.canAddRows,
        canDeleteRows: this.field.canDeleteRows
      }
    },

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

    canAddRow() {
      if (this.row.minRows !== null && this.row.maxRows !== null && this.row.minRows !== undefined && this.row.maxRows !== undefined) {
        return this.row.rowCount >= this.row.minRows && this.row.rowCount <
          this.row.maxRows && this.row.minRows !== this.row.maxRows && this.row.canAddRows
          && this.row.minRows > 0 && this.row.maxRows > 0;
      } else if (this.row.minRows !== null  && this.row.minRows !== undefined) {
        return this.row.rowCount >= this.row.minRows && this.row.canAddRows
          && this.row.minRows >= 0;
      } else if (this.row.maxRows !== null && this.row.maxRows !== undefined) {
        return this.row.rowCount < this.row.maxRows && this.row.canAddRows
          && this.row.maxRows > 0;
      }
      return true;
    },

    canDeleteRow() {
      if (this.row.minRows !== null && this.row.maxRows !== null && this.row.minRows !== undefined && this.row.maxRows !== undefined) {
        return this.row.rowCount <= this.row.maxRows && this.row.rowCount >
          this.row.minRows && this.row.canDeleteRows;
      } else if (this.row.minRows !== null && this.row.minRows !== undefined) {
        return this.row.rowCount > this.row.minRows && this.row.canDeleteRows;
      } else if (this.row.maxRows !== null && this.row.maxRows !== undefined) {
        return this.row.rowCount <= this.row.maxRows && this.row.canDeleteRows;
      }
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
      > * {
        flex: 1;
        flex-shrink: 0;
        min-width: 0px;
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

  > *:not(:only-child) {
    > :nth-child(1) {
      min-width: 20%;
    }

    // Make field area full width
    > :nth-child(2) {
      width: 100% !important;
      margin-right: 24px;
    }
  }

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