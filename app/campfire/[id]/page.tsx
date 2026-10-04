import type { Metadata } from "next";
import { CampfireDetail } from "@/components/gosolo/campfire";

export const metadata: Metadata = { title: "Campfire" };

export default async function Page({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  return <CampfireDetail id={id} />;
}
