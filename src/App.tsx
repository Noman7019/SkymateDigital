import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Route, Routes } from "react-router-dom";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { Toaster } from "@/components/ui/toaster";
import { TooltipProvider } from "@/components/ui/tooltip";
import Layout from "@/components/layout/Layout";
import SmoothScroll from "@/components/layout/SmoothScroll";
import Home from "./pages/Home";
import About from "./pages/About";
import Services from "./pages/Services";
import Testimonials from "./pages/Testimonials";
import Contact from "./pages/Contact";
import Portfolio from "./pages/Portfolio";
import NotFound from "./pages/NotFound";
import Terms from "./pages/Terms Condition";
import Privacy from "./pages/Privacy Policy";
import ServiceWebsiteDevelopment from "./pages/ServiceWebsiteDevelopment";
import ServiceAppDevelopment from "./pages/ServiceAppDevelopment";
import ServiceDigitalMarketing from "./pages/ServiceDigitalMarketing";
import ServiceAutomationAI from "./pages/ServiceAutomationAI";
import ServiceWebsiteMaintenance from "./pages/ServiceWebsiteMaintenance";
import ServiceSeoContentWriting from "./pages/ServiceSeoContentWriting";
import ContentCreation from "./pages/ContentCreationPortfolio";

const queryClient = new QueryClient();

const App = () => (
  <QueryClientProvider client={queryClient}>
    <TooltipProvider>
      <Toaster />
      <Sonner />
      <SmoothScroll>
        <BrowserRouter>
          <Layout>
            <Routes>
              <Route path="/" element={<Home />} />
              <Route path="/about" element={<About />} />
              <Route path="/portfolio" element={<Portfolio />} />
              <Route path="/services" element={<Services />} />
              <Route path="/testimonials" element={<Testimonials />} />
              <Route path="/contact-us" element={<Contact />} />
              <Route path="/services/website-development" element={<ServiceWebsiteDevelopment />} />
              <Route path="/services/app-development" element={<ServiceAppDevelopment />} />
              <Route path="/services/digital-marketing" element={<ServiceDigitalMarketing />} />
              <Route path="/services/automation-ai" element={<ServiceAutomationAI />} />
              <Route path="/services/website-maintenance" element={<ServiceWebsiteMaintenance />} />
              <Route path="/services/seo-content-writing" element={<ServiceSeoContentWriting />} />
              <Route path="/terms-condition" element={<Terms/>} />
              <Route path="/terms-of-service" element={<Terms/>} />
              <Route path="/content-creation" element={<ContentCreation />} />
              <Route path="/privacy-policy" element={<Privacy />} />
              <Route path="*" element={<NotFound />} />
            </Routes>
          </Layout>
        </BrowserRouter>
      </SmoothScroll>
    </TooltipProvider>
  </QueryClientProvider>
);

export default App;
