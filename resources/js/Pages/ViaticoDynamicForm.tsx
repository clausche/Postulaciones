// resources/js/Pages/ViaticoDynamicForm.jsx
import { Head, usePage } from '@inertiajs/react';
import { useState } from 'react';

type ViaticoDynamicFormProps = {
    placeholders?: string[];
    typeHints?: { [key: string]: string };
};

export default function ViaticoDynamicForm({ placeholders = [], typeHints = {} }: ViaticoDynamicFormProps) {
    const { props } = usePage();
    const csrf: string = (props?.csrf_token as string) || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const initial = Object.fromEntries(placeholders.map((p) => [p, '']));
    const [form, setForm] = useState(initial);
    const onChange = (e: React.ChangeEvent<HTMLInputElement>) => setForm({ ...form, [e.target.name]: e.target.value });

    // Submit nativo => descarga inmediata
    return (
        <div className="mx-auto max-w-3xl p-6">
            <Head title="Generar Viático" />
            <h1 className="mb-4 text-2xl font-bold">Generar documento de Viático</h1>
            <p className="mb-4 text-sm text-gray-600">Los campos se generan automáticamente desde la plantilla DOCX.</p>

            <form action={route('viatico.generar')} method="POST">
                <input type="hidden" name="_token" value={csrf} />

                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                    {placeholders.map((name: string) => {
                        const type = typeHints[name] || 'text';
                        const label = name.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
                        return (
                            <label key={name} className="flex flex-col gap-1">
                                <span className="text-sm font-medium">{label}</span>
                                <input name={name} type={type} className="rounded border p-2" value={form[name]} onChange={onChange} required />
                            </label>
                        );
                    })}
                </div>

                <div className="mt-6">
                    <button type="submit" className="rounded bg-blue-600 px-4 py-2 text-white">
                        Descargar DOCX
                    </button>
                </div>
            </form>
        </div>
    );
}
