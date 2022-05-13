import FormField from './components/FormField';
import DetailField from './components/DetailField';

Nova.booting((app, store) => {
  app.component('detail-simple-repeatable', DetailField)
  app.component('form-simple-repeatable', FormField)
})
