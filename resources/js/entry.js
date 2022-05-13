import DetailField from './components/DetailField';
import FormField from './components/FormField';

Nova.booting((Vue, store) => {
  Vue.component('detail-simple-repeatable', DetailField);
  Vue.component('form-simple-repeatable', FormField);
});
