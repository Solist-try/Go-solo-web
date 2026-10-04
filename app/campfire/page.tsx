import type { Metadata } from "next";
import { CampfireIndex } from "@/components/gosolo/campfire";

export const metadata: Metadata = { title: "Campfire" };

export default function Page() {
  return <CampfireIndex />;
}
