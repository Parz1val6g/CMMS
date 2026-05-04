export default function Form({ schema, tableName, initialData, onSubmit }) {
    // Uses Inertia's useForm hook to manage all inputs dynamically
    const { data, setData, post, errors } = useForm(initialData);

    return (
        <form onSubmit={/* handle submit */}>
            <h2 className="text-xl font-bold mb-6">Create {tableName}</h2>

            {Object.entries(schema).map(([contextName, fields]) => (
                <div key={contextName} className="mb-8 border-t border-slate-700 pt-4">
                    {/* The Context Header */}
                    <h3 className="text-lg text-slate-300 font-semibold mb-4">{contextName}</h3>
                    
                    {/* The Grid for the Inputs */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {Object.entries(fields).map(([fieldName, config]) => (
                            <div key={fieldName} className={`col-span-1 md:col-span-${config.col_span || 1}`}>
                                <label className="block text-sm text-slate-400">{config.label}</label>
                                
                                {/* The Input Function */}
                                <InputFactory 
                                    fieldName={fieldName}
                                    fieldConfig={config}
                                    value={data[fieldName]}
                                    onChange={(e) => setData(fieldName, e.target.value)}
                                    error={errors[fieldName]}
                                />
                                {errors[fieldName] && <span className="text-red-500 text-xs">{errors[fieldName]}</span>}
                            </div>
                        ))}
                    </div>
                </div>
            ))}
            
            <button type="submit" className="mt-4 bg-blue-600 text-white py-2 px-4 rounded">Save</button>
        </form>
    );
}