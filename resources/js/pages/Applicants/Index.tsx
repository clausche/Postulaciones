// 1. IMPORTACIONES
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { Megaphone, Pencil, Trash2 } from 'lucide-react';
import { FormEventHandler } from 'react';

import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';

// Importamos los tipos DESDE nuestro manual corregido
import { type Applicant, type BreadcrumbItem, type PageProps, type PaginatedResponse } from '@/types';

// 2. DEFINICIÓN DE PROPS (Para la paginación)
interface Props {
    applicants: PaginatedResponse<Applicant>;
}

// 3. DATOS ESTÁTICOS
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Becas 2025', href: route('applicants.index') }];

// 4. EL COMPONENTE PRINCIPAL
export default function Index({ applicants }: Props) {
    const { props: pageProps } = usePage<PageProps>();
    const { delete: destroy, processing } = useForm();

    const handleDelete: (id: number) => FormEventHandler<HTMLFormElement> = (id) => (e) => {
        e.preventDefault();
        if (confirm('¿Estás seguro de que quieres eliminar este postulante?')) {
            destroy(route('applicants.destroy', id));
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Postulantes" />
            <div className="space-y-4 p-4 sm:p-6 lg:p-8">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Listado de Postulantes</h1>
                    <Button asChild>
                        <Link href={route('applicants.create')}>Crear Postulante</Link>
                    </Button>
                </div>

                {pageProps.flash?.message && (
                    <Alert>
                        <Megaphone className="h-4 w-4" />
                        <AlertTitle>¡Éxito!</AlertTitle>
                        <AlertDescription>{pageProps.flash.message}</AlertDescription>
                    </Alert>
                )}

                <div className="rounded-md border">
                    <Table>
                        <TableCaption>Una lista de todos los postulantes registrados para el año 2025.</TableCaption>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="w-[100px]">Folio</TableHead>
                                <TableHead>Nombre Completo</TableHead>
                                <TableHead>Institución</TableHead>
                                <TableHead>Puntaje</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead className="text-right">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {applicants.data.map((applicant) => (
                                <TableRow key={applicant.id}>
                                    <TableCell className="font-medium">{applicant.folio}</TableCell>
                                    <TableCell>{applicant.full_name}</TableCell>

                                    {/* --- SOLUCIÓN A PRUEBA DE BALAS --- */}
                                    {/* Si 'institution' es undefined, no se romperá y mostrará el texto alternativo. */}
                                    <TableCell>{applicant.institution?.name}</TableCell>
                                    <TableCell>{applicant.score ?? 'N/A'}</TableCell>
                                    <TableCell>{applicant.status?.name}</TableCell>
                                    <TableCell className="text-right">
                                        <div className="flex justify-end gap-2">
                                            <Button variant="outline" size="icon" asChild>
                                                <Link href={route('applicants.edit', applicant.id)}>
                                                    <Pencil className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <form onSubmit={handleDelete(applicant.id)}>
                                                <Button variant="destructive" size="icon" type="submit" disabled={processing}>
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </form>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                <div className="flex items-center justify-center space-x-2 pt-4">
                    {applicants.links.map((link, index) => (
                        <Button key={index} variant={link.active ? 'default' : 'outline'} size="sm" asChild={!!link.url} disabled={!link.url}>
                            <Link href={link.url ?? ''} dangerouslySetInnerHTML={{ __html: link.label }} />
                        </Button>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
