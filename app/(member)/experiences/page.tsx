import type { Metadata } from "next";
import { Horizon } from "@/components/gosolo/horizon";

export const metadata: Metadata = { title: "Experiences" };

export default function Page() {
  return <Horizon kind="experiences" />;
}
