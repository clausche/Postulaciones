// resources/js/pages/Viaticos/create.tsx
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import React from 'react';
import { route } from 'ziggy-js'; // <- named import for Ziggy v1+

type Errors = Record<string, string[]>;

export default function Create({}: PageProps) {
    // ✅ Hook dentro del componente
    const { csrfToken } = usePage<PageProps & { csrfToken?: string }>().props;
    // Fallback al <meta> por si acaso
    const metaToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '';
    const effectiveCsrf = csrfToken ?? metaToken;

    const [data, setData] = React.useState({
        sector_salida: '',
        motivo_salida: '',
        fecha_salida: '',
        hora_salida: '',
        fecha_llegada: '',
        hora_llegada: '',
    });
    const [processing, setProcessing] = React.useState(false);
    const [errors, setErrors] = React.useState<Errors>({});

    function setField<K extends keyof typeof data>(key: K, value: string) {
        setData((prev) => ({ ...prev, [key]: value }));
        setErrors((prev) => ({ ...prev, [key]: [] }));
    }

    async function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        setProcessing(true);
        setErrors({});

        try {
            const url = route('viatico.generate');

            const resp = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': effectiveCsrf, // ✅ usar el token efectivo
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/octet-stream, application/json',
                },
                credentials: 'same-origin', // ✅ envía cookies de sesión
                body: JSON.stringify(data),
            });

            if (resp.ok) {
                const blob = await resp.blob();
                const dlUrl = URL.createObjectURL(blob);
                const a = document.createElement('a');
                const cd = resp.headers.get('Content-Disposition') || '';
                const match = /filename="([^"]+)"/i.exec(cd);
                a.href = dlUrl;
                a.download = match?.[1] ?? 'Viatico.docx';
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(dlUrl);
            } else if (resp.status === 422) {
                const payload = await resp.json();
                setErrors(payload.errors || {});
            } else {
                const payload = await resp.json().catch(() => ({}));
                alert((payload.message || 'Error al generar el documento.') + (payload.error ? `\n${payload.error}` : ''));
            }
        } catch {
            alert('Error de red o del servidor.');
        } finally {
            setProcessing(false);
        }
    }

    return (
        <AppLayout>
            <Head title="Generar Viático" />
            <h2 className="text-xl leading-tight font-semibold text-gray-800">Generador de Documento de Viático Rápido</h2>
            <div className="py-12">
                <div className="mx-auto max-w-2xl sm:px-6 lg:px-8">
                    <Card>
                        <CardHeader>
                            <CardTitle>Complete los datos de la comisión</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div>
                                    <Label htmlFor="sector_salida">Lugar del cometido</Label>
                                    <Input
                                        id="sector_salida"
                                        value={data.sector_salida}
                                        onChange={(e) => setField('sector_salida', e.target.value)}
                                    />
                                    {errors.sector_salida?.length ? <p className="mt-2 text-sm text-red-600">{errors.sector_salida[0]}</p> : null}
                                </div>

                                <div>
                                    <Label htmlFor="motivo_salida">Motivo de la comisión</Label>
                                    <Textarea
                                        id="motivo_salida"
                                        value={data.motivo_salida}
                                        onChange={(e) => setField('motivo_salida', e.target.value)}
                                    />
                                    {errors.motivo_salida?.length ? <p className="mt-2 text-sm text-red-600">{errors.motivo_salida[0]}</p> : null}
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label htmlFor="fecha_salida">Fecha de Salida</Label>
                                        <Input
                                            type="date"
                                            id="fecha_salida"
                                            value={data.fecha_salida}
                                            onChange={(e) => setField('fecha_salida', e.target.value)}
                                        />
                                        {errors.fecha_salida?.length ? <p className="mt-2 text-sm text-red-600">{errors.fecha_salida[0]}</p> : null}
                                    </div>
                                    <div>
                                        <Label htmlFor="fecha_llegada">Fecha de Regreso</Label>
                                        <Input
                                            type="date"
                                            id="fecha_llegada"
                                            value={data.fecha_llegada}
                                            onChange={(e) => setField('fecha_llegada', e.target.value)}
                                        />
                                        {errors.fecha_llegada?.length ? <p className="mt-2 text-sm text-red-600">{errors.fecha_llegada[0]}</p> : null}
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label htmlFor="hora_salida">Hora de Salida</Label>
                                        <Input
                                            type="time"
                                            id="hora_salida"
                                            value={data.hora_salida}
                                            onChange={(e) => setField('hora_salida', e.target.value)}
                                        />
                                        {errors.hora_salida?.length ? <p className="mt-2 text-sm text-red-600">{errors.hora_salida[0]}</p> : null}
                                    </div>
                                    <div>
                                        <Label htmlFor="hora_llegada">Hora de Regreso</Label>
                                        <Input
                                            type="time"
                                            id="hora_llegada"
                                            value={data.hora_llegada}
                                            onChange={(e) => setField('hora_llegada', e.target.value)}
                                        />
                                        {errors.hora_llegada?.length ? <p className="mt-2 text-sm text-red-600">{errors.hora_llegada[0]}</p> : null}
                                    </div>
                                </div>

                                <div className="mt-4 flex items-center justify-end">
                                    <Button
                                        type="submit"
                                        disabled={
                                            processing ||
                                            !data.sector_salida ||
                                            !data.motivo_salida ||
                                            !data.fecha_salida ||
                                            !data.hora_salida ||
                                            !data.fecha_llegada ||
                                            !data.hora_llegada
                                        }
                                    >
                                        {processing ? 'Generando...' : 'Generar Documento Word'}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
