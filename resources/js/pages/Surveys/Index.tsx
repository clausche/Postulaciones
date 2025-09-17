// Ruta: resources/js/pages/surveys/index.tsx

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import AppLayout from '@/layouts/app-layout';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';
import { HardDrive, Server, Tv, Users } from 'lucide-react';
import { Bar, BarChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

// Definimos con TypeScript la estructura de los datos que recibimos del controlador
type Stats = {
    total_municipalities: number;
    surveys_completed: number;
    avg_computers: number;
    cloud_adoption_percentage: number;
    streaming_adoption_percentage: number;
    main_internet_providers: {
        proveedor_internet_principal: string;
        total: number;
    }[];
};

export default function Index({ auth, stats }: PageProps<{ stats: Stats }>) {
    const completionPercentage = Math.round((stats.surveys_completed / stats.total_municipalities) * 100);

    return (
        <AppLayout header={<h2 className="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Dashboard de Encuestas 2023</h2>}>
            <Head title="Dashboard de Encuestas" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        {/* --- Tarjetas de Estadísticas Principales --- */}
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Municipios Totales</CardTitle>
                                <Users className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.total_municipalities}</div>
                                <p className="text-xs text-muted-foreground">Encuestas completadas: {stats.surveys_completed}</p>
                                <Progress value={completionPercentage} className="mt-2" />
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Promedio de Computadores</CardTitle>
                                <HardDrive className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.avg_computers}</div>
                                <p className="text-xs text-muted-foreground">por municipalidad</p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Adopción de Cloud</CardTitle>
                                <Server className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.cloud_adoption_percentage}%</div>
                                <p className="text-xs text-muted-foreground">de los municipios usan servidores cloud</p>
                                <Progress value={stats.cloud_adoption_percentage} className="mt-2" />
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Transmisión de Sesiones</CardTitle>
                                <Tv className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.streaming_adoption_percentage}%</div>
                                <p className="text-xs text-muted-foreground">de los municipios transmiten sus sesiones</p>
                                <Progress value={stats.streaming_adoption_percentage} className="mt-2" />
                            </CardContent>
                        </Card>
                    </div>

                    {/* --- Gráfico de Barras --- */}
                    <div className="mt-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Top 5 Proveedores de Internet</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <ResponsiveContainer width="100%" height={350}>
                                    <BarChart data={stats.main_internet_providers}>
                                        <CartesianGrid strokeDasharray="3 3" />
                                        <XAxis dataKey="proveedor_internet_principal" />
                                        <YAxis />
                                        <Tooltip />
                                        <Bar dataKey="total" fill="#8884d8" name="Nº de Municipios" />
                                    </BarChart>
                                </ResponsiveContainer>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
