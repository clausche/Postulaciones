// resources/js/pages/Viaticos/create_new.tsx
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import React from 'react';
import { route } from 'ziggy-js'; // Ziggy v1+

/**
 * Formulario de Viático (versión create_new)
 * - Integrado al starter kit (AppLayout, shadcn/ui, Inertia, Ziggy)
 * - Recuerda campos recurrentes (incluye fechas y horas) con localStorage
 * - Botón para rellenar con datos anteriores / borrar recordados
 */

// === Persistencia local ===
const LS_KEY = 'viatico:lastValues:v2';
const STICKY_FIELDS = [
  'sector_salida',
  'motivo_salida',
  'fecha_salida',
  'hora_salida',
  'fecha_llegada',
  'hora_llegada',
] as const;

type Errors = Record<string, string[]>;

type FormData = {
  sector_salida: string;
  motivo_salida: string;
  fecha_salida: string; // yyyy-mm-dd
  hora_salida: string;  // HH:mm
  fecha_llegada: string; // yyyy-mm-dd
  hora_llegada: string;  // HH:mm
};

type Props = PageProps & { csrfToken?: string };

export default function CreateNew({}: Props) {
  // CSRF desde props de Inertia o desde <meta>
  const { csrfToken } = usePage<Props>().props;
  const metaToken =
    document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '';
  const effectiveCsrf = csrfToken ?? metaToken;

  const [data, setData] = React.useState<FormData>({
    sector_salida: '',
    motivo_salida: '',
    fecha_salida: '',
    hora_salida: '',
    fecha_llegada: '',
    hora_llegada: '',
  });

  const [processing, setProcessing] = React.useState(false);
  const [errors, setErrors] = React.useState<Errors>({});
  const [rememberOnSubmit, setRememberOnSubmit] = React.useState(true);
  const [hasStored, setHasStored] = React.useState(false);

  // Detectar si hay guardados
  React.useEffect(() => {
    try { setHasStored(!!localStorage.getItem(LS_KEY)); } catch {}
  }, []);

  function setField<K extends keyof FormData>(key: K, value: string) {
    setData((prev) => ({ ...prev, [key]: value }));
    setErrors((prev) => ({ ...prev, [key]: [] }));
  }

  // Aplicar guardados (solo STICKY_FIELDS)
  function applyStored() {
    try {
      const raw = localStorage.getItem(LS_KEY);
      if (!raw) return;
      const saved = JSON.parse(raw) as Partial<FormData>;
      const next: FormData = { ...data };
      (STICKY_FIELDS as readonly (keyof FormData)[]).forEach((k) => {
        if (saved[k]) (next as any)[k] = saved[k] as string;
      });
      setData(next);
    } catch {}
  }

  function persistSticky(payload: FormData) {
    try {
      const subset: Partial<FormData> = {};
      (STICKY_FIELDS as readonly (keyof FormData)[]).forEach((k) => {
        subset[k] = payload[k];
      });
      localStorage.setItem(LS_KEY, JSON.stringify(subset));
      setHasStored(true);
    } catch {}
  }

  function clearStored() {
    try { localStorage.removeItem(LS_KEY); setHasStored(false); } catch {}
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
          'X-CSRF-TOKEN': effectiveCsrf,
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/octet-stream, application/json',
        },
        credentials: 'same-origin',
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
        if (rememberOnSubmit) persistSticky(data);
      } else if (resp.status === 422) {
        const payload = await resp.json();
        setErrors(payload.errors || {});
      } else {
        const payload = await resp.json().catch(() => ({}));
        alert(
          (payload.message || 'Error al generar el documento.') +
            (payload.error ? `\n${payload.error}` : '')
        );
      }
    } catch (err) {
      alert('Error de red o del servidor.');
    } finally {
      setProcessing(false);
    }
  }

  return (
    <AppLayout>
      <Head title="Generar Viático" />
      <h2 className="text-xl leading-tight font-semibold text-gray-800">
        Generador de Documento de Viático Rápido
      </h2>

      <div className="py-12">
        <div className="mx-auto max-w-2xl sm:px-6 lg:px-8">
          <Card>
            <CardHeader>
              <CardTitle>Complete los datos de la comisión</CardTitle>
            </CardHeader>
            <CardContent>
              {/* Controles de recordar */}
              <div className="mb-4 flex flex-wrap items-center gap-3">
                <button
                  type="button"
                  onClick={applyStored}
                  disabled={!hasStored}
                  className={`text-sm underline ${!hasStored ? 'opacity-50 cursor-not-allowed' : ''}`}
                  title={hasStored ? 'Rellenar con datos anteriores' : 'No hay datos guardados'}
                >
                  Rellenar con datos anteriores
                </button>
                <label className="text-sm flex items-center gap-2">
                  <input
                    type="checkbox"
                    checked={rememberOnSubmit}
                    onChange={(e) => setRememberOnSubmit(e.target.checked)}
                  />
                  Recordar estos datos al enviar
                </label>
                {hasStored && (
                  <button type="button" onClick={clearStored} className="text-sm underline">
                    Borrar recordados
                  </button>
                )}
              </div>

              <form onSubmit={handleSubmit} className="space-y-6">
                <div>
                  <Label htmlFor="sector_salida">Lugar del cometido</Label>
                  <Input
                    id="sector_salida"
                    value={data.sector_salida}
                    onChange={(e) => setField('sector_salida', e.target.value)}
                  />
                  {errors.sector_salida?.length ? (
                    <p className="mt-2 text-sm text-red-600">{errors.sector_salida[0]}</p>
                  ) : null}
                </div>

                <div>
                  <Label htmlFor="motivo_salida">Motivo de la comisión</Label>
                  <Textarea
                    id="motivo_salida"
                    value={data.motivo_salida}
                    onChange={(e) => setField('motivo_salida', e.target.value)}
                  />
                  {errors.motivo_salida?.length ? (
                    <p className="mt-2 text-sm text-red-600">{errors.motivo_salida[0]}</p>
                  ) : null}
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
                    {errors.fecha_salida?.length ? (
                      <p className="mt-2 text-sm text-red-600">{errors.fecha_salida[0]}</p>
                    ) : null}
                  </div>
                  <div>
                    <Label htmlFor="fecha_llegada">Fecha de Regreso</Label>
                    <Input
                      type="date"
                      id="fecha_llegada"
                      value={data.fecha_llegada}
                      onChange={(e) => setField('fecha_llegada', e.target.value)}
                    />
                    {errors.fecha_llegada?.length ? (
                      <p className="mt-2 text-sm text-red-600">{errors.fecha_llegada[0]}</p>
                    ) : null}
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
                    {errors.hora_salida?.length ? (
                      <p className="mt-2 text-sm text-red-600">{errors.hora_salida[0]}</p>
                    ) : null}
                  </div>
                  <div>
                    <Label htmlFor="hora_llegada">Hora de Regreso</Label>
                    <Input
                      type="time"
                      id="hora_llegada"
                      value={data.hora_llegada}
                      onChange={(e) => setField('hora_llegada', e.target.value)}
                    />
                    {errors.hora_llegada?.length ? (
                      <p className="mt-2 text-sm text-red-600">{errors.hora_llegada[0]}</p>
                    ) : null}
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
