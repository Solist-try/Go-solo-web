import type { Metadata } from "next";
import { WaypointIndex } from "@/components/gosolo/waypoints";

export const metadata: Metadata = {
  title: "Waypoints",
  description: "Places where people exploring similar parts of life gather and continue.",
};

export default function Page() {
  return <WaypointIndex />;
}
