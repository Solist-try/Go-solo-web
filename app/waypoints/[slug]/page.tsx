import type { Metadata } from "next";
import { WaypointDetail } from "@/components/gosolo/waypoints";
import { getWaypoint } from "@/lib/catalog";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ slug: string }>;
}): Promise<Metadata> {
  const { slug } = await params;
  const waypoint = getWaypoint(slug);
  return { title: waypoint?.name ?? "Waypoint", description: waypoint?.summary };
}

export default async function Page({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  return <WaypointDetail slug={slug} />;
}
