// Ruta del archivo: resources/js/pages/surveys/show.tsx

import AppLayout from '@/layouts/app-layout';
import { PageProps } from '@/types';
import { Head, Link } from '@inertiajs/react';

// Definimos los tipos para los datos que recibimos del controlador
// Esto es más detallado porque recibimos la encuesta completa
type Provider = { id: number; nombre: string };
type Platform = { id: number; nombre: string };
type Municipality = { id: number; nombre: string };

type SurveyDetails = {
    id: number;
    ano_encuesta: number;
    velocidad_subida: string;
    velocidad_bajada: string;
    // ... (puedes añadir todos los demás campos aquí si quieres)
    municipality: Municipality;
    providers: Provider[];
    platforms: Platform[];
};

export default function Show({ auth, survey }: PageProps<{ survey: SurveyDetails }>) {
    return (
        <AppLayout
            user={auth.user}
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">
                        Detalle de Encuesta: {survey.municipality.nombre}
                    </h2>
                    <Link href={route('surveys.index')} className="rounded-md bg-gray-500 px-4 py-2 font-bold text-white hover:bg-gray-600">
                        Volver al Listado
                    </Link>
                </div>
            }
        >
            <Head title={`Detalle: ${survey.municipality.nombre}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="grid grid-cols-1 gap-6 p-6 text-gray-900 md:grid-cols-2 dark:text-gray-100">
                            {/* Columna Izquierda: Datos Generales */}
                            <div className="space-y-4">
                                <h3 className="border-b border-gray-300 pb-2 text-lg font-bold dark:border-gray-600">Datos Generales</h3>
                                <p>
                                    <strong>Año de Encuesta:</strong> {survey.ano_encuesta}
                                </p>
                                <p>
                                    <strong>Velocidad de Subida:</strong> {survey.velocidad_subida || 'No informado'}
                                </p>
                                <p>
                                    <strong>Velocidad de Bajada:</strong> {survey.velocidad_bajada || 'No informado'}
                                </p>
                            </div>

                            {/* Columna Derecha: Relaciones */}
                            <div className="space-y-4">
                                <h3 className="border-b border-gray-300 pb-2 text-lg font-bold dark:border-gray-600">Proveedores y Plataformas</h3>

                                <div>
                                    <strong>Proveedores de Sistemas Externos:</strong>
                                    <ul className="mt-1 list-inside list-disc">
                                        {survey.providers.length > 0 ? (
                                            survey.providers.map((provider) => <li key={provider.id}>{provider.nombre}</li>)
                                        ) : (
                                            <li>No informado</li>
                                        )}
                                    </ul>
                                </div>

                                <div>
                                    <strong>Plataformas de Streaming:</strong>
                                    <ul className="mt-1 list-inside list-disc">
                                        {survey.platforms.length > 0 ? (
                                            survey.platforms.map((platform) => <li key={platform.id}>{platform.nombre}</li>)
                                        ) : (
                                            <li>No informado</li>
                                        )}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
