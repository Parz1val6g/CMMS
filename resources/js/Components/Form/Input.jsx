export default function Input({ fieldName, fieldConfig, value, onChange, error }) {
    switch (fieldConfig.type) {
        case 'select':
            return <SelectInput name={fieldName} options={fieldConfig.options} value={value} onChange={onChange} />;
        case 'textarea':
            return <Textarea name={fieldName} placeholder={fieldConfig.placeholder} value={value} onChange={onChange} />;
        case 'number':
        case 'text':
        default:
            return <TextInput type={fieldConfig.type} name={fieldName} value={value} onChange={onChange} />;
    }
}