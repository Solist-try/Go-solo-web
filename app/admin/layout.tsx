import type { Metadata } from "next";
import type { ReactNode } from "react";
import { AdminGate } from "@/components/gosolo/admin/gate";

export const metadata: Metadata = {
  title: "Steward",
  description: "A calm desk for tending Go Solo.",
};

export default function AdminLayout({ children }: { children: ReactNode }) {
  return <AdminGate>{children}</AdminGate>;
}
