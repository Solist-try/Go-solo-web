import { WaypointEditor } from "@/components/gosolo/admin/waypoints";

export default async function Page({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  return <WaypointEditor id={id} />;
}
