import type { Metadata } from "next";
import { Horizon } from "@/components/gosolo/horizon";

export const metadata: Metadata = { title: "First Night Kits" };

export default function Page() {
  return <Horizon kind="kits" />;
}
