// resources/js/Pages/Viaticos/Create.tsx
import { Head, usePage } from '@inertiajs/react';
import React, { ReactNode, useState } from 'react';
import { route } from 'ziggy-js';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';

type Props = {
    vehiculos: string[];
    defaultVehiculo: string;
    csrf_token: string; // viene de HandleInertiaRequests@share
};

// Tipo auxiliar para permitir .layout sin pelear con TS
interface InertiaPageComponent<P = {}> extends React.FC<P> {
    layout?: (page: ReactNode) => ReactNode;
}

const Create: InertiaPageComponent = () => {
    const { vehiculos, defaultVehiculo, csrf_token } = usePage<Props>().props;
    const [vehiculo, setVehiculo] = useState(defaultVehiculo ?? 'M05');
    const [hoy] = useState(() => new Date().toISOString().slice(0, 10)); // yyyy-mm-dd

    return (
        <>
            <Head title="Nuevo Viático" />

            <div className="mx-auto w-full max-w-4xl space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Nuevo Viático</CardTitle>
                    </CardHeader>

                    {/* POST clásico para descargar el DOCX */}
                    <form method="POST" action={route('viaticos.store')}>
                        <input type="hidden" name="_token" value={csrf_token} />

                        <CardContent className="space-y-6">
                            {/* Identificación */}
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <Label htmlFor="nombre">Nombre *</Label>
                                    <Input id="nombre" name="nombre" required placeholder="Nombre completo" />
                                </div>
                                <div>
                                    <Label htmlFor="rut">RUT *</Label>
                                    <Input id="rut" name="rut" required placeholder="11.111.111-1" />
                                </div>
                                <div>
                                    <Label htmlFor="escalafon">Escalafón</Label>
                                    <Input id="escalafon" name="escalafon" />
                                </div>
                                <div>
                                    <Label htmlFor="grado">Grado</Label>
                                    <Input id="grado" name="grado" />
                                </div>
                                <div className="md:col-span-2">
                                    <Label htmlFor="funcion">Función</Label>
                                    <Input id="funcion" name="funcion" />
                                </div>
                            </div>

                            {/* Comisión */}
                            <div className="space-y-4">
                                <div>
                                    <Label htmlFor="lugar">Lugar *</Label>
                                    <Input id="lugar" name="lugar" required placeholder="Destino del cometido" />
                                </div>
                                <div>
                                    <Label htmlFor="motivo">Motivo (máx. 500) *</Label>
                                    <Textarea id="motivo" name="motivo" required maxLength={500} />
                                </div>
                            </div>

                            {/* Fechas y horas */}
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <Label htmlFor="dia_salida">Día de salida *</Label>
                                    <Input id="dia_salida" type="date" name="dia_salida" defaultValue={hoy} required />
                                </div>
                                <div>
                                    <Label htmlFor="hora_salida">Hora de salida *</Label>
                                    <Input id="hora_salida" type="time" name="hora_salida" required />
                                </div>
                                <div>
                                    <Label htmlFor="dia_regreso">Día de regreso *</Label>
                                    <Input id="dia_regreso" type="date" name="dia_regreso" defaultValue={hoy} required />
                                </div>
                                <div>
                                    <Label htmlFor="hora_regreso">Hora de regreso *</Label>
                                    <Input id="hora_regreso" type="time" name="hora_regreso" required />
                                </div>
                            </div>

                            {/* Transporte / Resolución */}
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <Label htmlFor="vehiculo">Vehículo *</Label>
                                    <select
                                        id="vehiculo"
                                        name="vehiculo"
                                        className="w-full rounded-md border px-3 py-2"
                                        value={vehiculo}
                                        onChange={(e) => setVehiculo(e.target.value)}
                                        required
                                    >
                                        {vehiculos?.map((v) => (
                                            <option key={v} value={v}>
                                                {v}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <Label htmlFor="patente">Patente</Label>
                                    <Input id="patente" name="patente" placeholder="AA-BB-11 / XXYY11" />
                                </div>
                                <div>
                                    <Label htmlFor="resolucion">N° Resolución</Label>
                                    <Input id="resolucion" name="resolucion" placeholder="ORD/RES-XXXX" />
                                </div>
                            </div>

                            <div className="pt-2">
                                <Button type="submit">Generar y descargar DOCX</Button>
                            </div>
                        </CardContent>
                    </form>
                </Card>
            </div>
        </>
    );
};

// Asigna layout y luego exporta UNA sola vez
Create.layout = (page: ReactNode) => <AppLayout title="Viáticos">{page}</AppLayout>;
export default Create;
