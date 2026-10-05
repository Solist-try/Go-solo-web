"use client";

import type { ReactNode } from "react";
import { Toaster } from "@/components/ui/sonner";
import { AuthProvider } from "@/lib/auth-context";
import { GoSoloProvider } from "@/lib/gosolo";

export function Providers({ children }: { children: ReactNode }) {
  return (
    <AuthProvider>
      <GoSoloProvider>
        {children}
        <Toaster theme="light" position="bottom-center" />
      </GoSoloProvider>
    </AuthProvider>
  );
}
