"use client";

import type { ReactNode } from "react";
import { Toaster } from "@/components/ui/sonner";
import { GoSoloProvider } from "@/lib/gosolo";

export function Providers({ children }: { children: ReactNode }) {
  return (
    <GoSoloProvider>
      {children}
      <Toaster theme="light" position="bottom-center" />
    </GoSoloProvider>
  );
}
