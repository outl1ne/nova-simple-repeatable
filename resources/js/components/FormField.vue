<template>
  <default-field :field="field" :errors="errors" :show-help-text="showHelpText" class="simple-repeatable">
    <template slot="field">
      <div class="flex flex-col">
        <!-- Title columns -->
        <div class="simple-repeatable-header-row flex border-b border-40 py-2">
          <div v-for="(repField, i) in field.repeatableFields" :key="i" class="font-bold text-90 text-md w-full mr-2">
            {{ repField.name }}
          </div>
        </div>

        <div v-for="(fields, i) in fieldsWithValues" :key="i" class="simple-repeatable-row flex py-2">
          <component
            v-for="(repField, i) in fields"
            :key="i"
            :is="`form-${repField.component}`"
            :field="repField"
            class="mr-2"
          />
        </div>

        <button @click="addRow" class="btn btn-default btn-primary" type="button">Add row</button>
      </div>
    </template>
  </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova';

export default {
  mixins: [FormField, HandlesValidationErrors],

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
        value = JSON.parse(this.field.value) || [];
      } catch (e) {
        value = [];
      }

      this.fieldsWithValues = value.map(this.copyFields);

      console.info(this.fieldsWithValues);
    },

    copyFields(value) {
      return this.field.repeatableFields.map(field => ({ ...field, value: value[field.attribute] }));
    },

    fill(formData) {
      const allValues = [];

      for (const fields of this.fieldsWithValues) {
        const rowValues = {};

        for (const field of fields) {
          const formData = new FormData();
          field.fill(formData);
          rowValues[field.attribute] = formData.get(field.attribute);
        }

        allValues.push(rowValues);
      }

      formData.append(this.field.attribute, JSON.stringify(allValues));
    },

    addRow() {
      this.fieldsWithValues.push(this.copyFields());
    },
  },

  computed: {
    defaultAttributes() {
      return {
        type: 'number',
        min: this.field.min,
        max: this.field.max,
        step: this.field.step,
        pattern: this.field.pattern,
        placeholder: this.field.placeholder || this.field.name,
        class: this.errorClasses,
      };
    },

    extraAttributes() {
      return {
        ...this.defaultAttributes,
        ...this.field.extraAttributes,
      };
    },
  },
};
</script>

<style lang="scss" scoped>
.simple-repeatable {
  .simple-repeatable-row {
    // Select field
    > * {
      width: 100%;

      // Hide name
      > :nth-child(1) {
        display: none;
      }

      // Fix field width and padding
      > :nth-child(2) {
        width: 100% !important;
        padding: 0 !important;
      }
    }
  }
}
</style>
